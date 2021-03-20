"use strict";

class RenderableEvent {

    constructor() {
        this._rectangles = [];
    }
}


// What should this class implement? Probably a render() method. And a way to
// convert between <mouse position> and <date & time>. 
class CalendarView {

    constructor() {
    }

}

class DayView extends CalendarView {
    constructor() {
        super();
    }

    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }

    keydown(e) {
    }

    mousedown(e) {
    }

    mouseup(e) {
    }

    mousemove(e) {
    }

    resize(e) {
    }
}

class WeekView extends CalendarView {
    constructor() {
        super();

        this.view_start_hour = 7;
        this.view_end_hour = 22;

        this.top_row_px = INIT_TOP_ROW_PX;
        this.left_col_px = INIT_LEFT_COL_PX;

        this.grid_idx = 3;
        this.grid_size = grid_presets[this.grid_idx];

        this.snap_to_grid = true;

        this.originOffsetFromSunday = 0;
        this.origin_date;
        this.next_origin_date;

        this.mousePosition = new Point();

        this.mouse_left_btn_down = false;
        this.mouse_right_btn_down = false;

        this.selected_event = null;
        this.selected_top = false;
        this.selected_bot = false;
        this.selected_event_moved = false;
        this.selectedEventInit = new Point();
        this.selectedEventFinal = new Point();

        this.new_event_active = false;
        this.new_event = {};
        this.newEventInitPosition = new Point();
        this.newEventFinalPosition = new Point();

        this.zoom_active = false;
        this.zoomInit = new Point();
        this.zoomFinal = new Point();
        this.zoom_start_hour, this.zoom_end_hour;

        this.sidebarVisible = false;
    }

    _getSidebarPx() {
        return this.sidebarVisible ? Math.floor(SIDEBAR_WIDTH_PERCENT * can_w) : 0;
    }

    _hours_in_view()        { return (this.view_end_hour - this.view_start_hour) + 1; }
    _colWidth()             { return Math.floor((can_w-this.left_col_px-this._getSidebarPx())/DAYS_IN_WEEK); }
    _hourHeight()           { return Math.floor((can_h-this.top_row_px)/this._hours_in_view()); }
    _hoursMinsToY(hour, min){ return Math.round(this.top_row_px + ((hour-this.view_start_hour)+min/60.0) * this._hourHeight()); }

    _dyTodt(dy) {
        const hours = (dy < 0 ? -1 : 1)*Math.floor(Math.abs(dy)/this._hourHeight());
        const minutes = (dy < 0 ? -1 : 1)*Math.round(60*(Math.abs(dy) % this._hourHeight())/this._hourHeight());
        return {hours, minutes};
    };

    _yToHour(y) { return Math.floor((y - this.top_row_px)/this._hourHeight()) + this.view_start_hour; }
    _yToMin(y) { return Math.round(60*((y - this.top_row_px) % this._hourHeight())/this._hourHeight()); }

    _colToDate(col) {
        const d = new Date(this.origin_date.getTime());
        d.setDate(d.getDate() + col);
        return d;
    }

    // TODO: handle x - left_col_px < 0 case
    _xToCol(x) { return Math.floor((x-this.left_col_px-this._getSidebarPx())/this._colWidth()); }

    _xyToDateTime(x, y) {
        const col = this._xToCol(x);
        const _date = this._colToDate(col);
        _date.setHours(this._yToHour(y));
        _date.setMinutes(this._yToMin(y));
        _date.setSeconds(0);
        _date.setMilliseconds(0);
        return _date;
    }

    _mousePosToDateTime() {
        return this._xyToDateTime(this.mousePosition.x, this.mousePosition.y);
    }

    _selectCurrentOrUpcomingEvent() {
        events.sort((a,b) => a.start_date.getTime() - b.start_date.getTime());
        // find the first event which ends after the current time
        for (const e of events) {
            if (e.end_date.getTime() > (new Date()).getTime()) {
                this.selected_event = e;
                break;
            }
        }
    }

    _selectNextEvent() {
        events.sort((a,b) => a.start_date.getTime() - b.start_date.getTime());
        for (const e of events) {
            if (e.start_date.getTime() > this.selected_event.start_date.getTime()) {
                this.selected_event = e;
                break;
            }
        }
    }

    _selectPrevEvent() {
        events.sort((a,b) => a.start_date.getTime() - b.start_date.getTime());
        let prev_event = events[0];
        for (const e of events) {
            if (e.start_date.getTime() >= this.selected_event.start_date.getTime()) {
                this.selected_event = prev_event;
                break;
            }
            prev_event = e;
        }
    }

    // TODO: this code is redundant with _setClickedEvent, eliminate redundancy
    _checkForEventHover(x, y) {
        let hoverInfo = {};
        hoverInfo.e = null;
        for (const e of events) {
            // skip checking events that are not in view
            if ((e.end_date < this.origin_date) || (e.start_date > this.next_origin_date)) {
                continue;
            }
            const e_col = inWeek(e.start_date.getDay() - this.originOffsetFromSunday);
            const e_x_min = this._getSidebarPx() + this.left_col_px + e_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1;
            const e_x_max = e_x_min + 0.9*this._colWidth() - e.layer*this._colWidth()*0.1;
            const e_y_min = Math.max(this._hoursMinsToY(e.start_date.getHours(), e.start_date.getMinutes()), this.top_row_px);
            const e_y_max = this._hoursMinsToY(e.end_date.getHours(), e.end_date.getMinutes());

            if ((x >= e_x_min && x <= e_x_max) && (y >= e_y_min && y <= e_y_max)) {
                if ((hoverInfo.e === null) || (hoverInfo.e.layer < e.layer)) {
                    hoverInfo.e = e;
                    hoverInfo.top    = (y >= e_y_min && y <= e_y_min + EDGE_SIZE) ? true : false;
                    hoverInfo.bottom = (y <= e_y_max && y >= e_y_max - EDGE_SIZE) ? true : false;
                }
            }
        }
        return hoverInfo;
    }

    // TODO: encapsulate the calculations used here
    _setClickedEvent(x, y) {
        this.selected_event = null;
        this.selected_event_moved = false;
        this.selected_top = false;
        this.selected_bot = false;
        for (const e of events) {
            // skip checking events that are not in view
            if ((e.end_date < this.origin_date) || (e.start_date > this.next_origin_date)) {
                continue;
            }
            let e_col = inWeek(e.start_date.getDay() - this.originOffsetFromSunday);
            const e_x_min = this._getSidebarPx() + this.left_col_px + e_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1;
            const e_x_max = e_x_min + 0.9*this._colWidth() - e.layer*this._colWidth()*0.1;
            const e_y_min = Math.max(this._hoursMinsToY(e.start_date.getHours(), e.start_date.getMinutes()), this.top_row_px);
            const e_y_max = this._hoursMinsToY(e.end_date.getHours(), e.end_date.getMinutes());

            if ((x >= e_x_min && x <= e_x_max) && (y >= e_y_min && y <= e_y_max)) {
                if ((this.selected_event === null) || (this.selected_event.layer < e.layer)) {
                    this.selected_event = e;
                    this.selected_top = (y >= e_y_min && y <= e_y_min + EDGE_SIZE) ? true : false;
                    this.selected_bot = (y <= e_y_max && y >= e_y_max - EDGE_SIZE) ? true : false;
                    //break;
                }
            }
        }
    }

    _checkIfEventsOverlap(e1, e2) {
        if ((e2.start_date.getTime() < e1.end_date.getTime()) && (e1.start_date.getTime() < e2.end_date.getTime())) {
            return true;
        } else {
            return false;
        }
    }

    _calcEventLayers() {
        events.sort((a,b) => a.layer - b.layer);
        for (let d = 0; d < DAYS_IN_WEEK; d++) {

            let day_in_week = new Date(this.origin_date.getTime());
            day_in_week.setDate(this.origin_date.getDate() + d);
            let next_day_in_week = new Date(this.origin_date.getTime());
            next_day_in_week.setDate(this.origin_date.getDate() + d+1);

            let eventsOnDay = [];
            for (const e of events) {
                // NOTE: doesn't handle events that span multiple days
                if ((e.start_date.getTime() > day_in_week.getTime()) &&
                    (e.start_date.getTime() < next_day_in_week.getTime())) {
                    eventsOnDay.push(e);
                }
            }
            eventsOnDay.sort((a,b) => a.start_date.getTime() - b.start_date.getTime());

            for (const e of eventsOnDay) {
                e.layer = 0;
                let overlap;
                do {
                    overlap = false;
                    let priorEventsInLayer = [];
                    for (const e2 of eventsOnDay) {
                        if (e2 === e) {
                            continue;
                        }
                        if ((e2.layer === e.layer) && (e2.start_date.getTime() <= e.start_date.getTime())) {
                            priorEventsInLayer.push(e2);
                        }
                    }
                    priorEventsInLayer.sort((a,b) => a.start_date.getTime() - b.start_date.getTime());

                    for (const e2 of priorEventsInLayer) {
                        if (this._checkIfEventsOverlap(e, e2)) {
                            overlap = true;
                            e.layer++;
                            break;
                        }
                    }
                } while(overlap);
            }
        }
    }

    // TODO: prevent moving start time past end time and vice versa
    _getModifiedTimes(e) {
        const dx = (e === this.selected_event && this.selected_event_moved) ? this.selectedEventFinal.x - this.selectedEventInit.x : 0;
        const dy = (e === this.selected_event && this.selected_event_moved) ? this.selectedEventFinal.y - this.selectedEventInit.y : 0;
        const dt = this._dyTodt(dy);

        const dest_start_date = new Date(e.start_date.getTime());
        if (!this.selected_bot) {
            dest_start_date.setHours(dest_start_date.getHours() + dt.hours);
            dest_start_date.setMinutes(dest_start_date.getMinutes() + dt.minutes);
        }

        const dest_end_date = new Date(e.end_date.getTime());
        if (!this.selected_top) {
            dest_end_date.setHours(dest_end_date.getHours() + dt.hours);
            dest_end_date.setMinutes(dest_end_date.getMinutes() + dt.minutes);
        }

        if (e === this.selected_event && this.selected_event_moved) {
            const init_col = this._xToCol(this.selectedEventInit.x);
            const final_col = this._xToCol(this.selectedEventFinal.x);
            const dcol = final_col - init_col;

            if (!this.selected_bot && !this.selected_top) {
                dest_start_date.setDate(dest_start_date.getDate() + dcol);
                dest_end_date.setDate(dest_end_date.getDate() + dcol);
            }
        }

        // snap to grid
        if (this.snap_to_grid && Math.abs(dy) > 0) {
            if (this.selected_top) {
                dest_start_date.roundN(this.grid_size);
            } else if (this.selected_bot) {
                dest_end_date.roundN(this.grid_size);
            } else {
                const rounded_off = dest_start_date.roundN(this.grid_size);
                dest_end_date.setMinutes(dest_end_date.getMinutes() + rounded_off);
            }
        }

        return [dest_start_date, dest_end_date];
    }

    _setNewEventStartEnd() {
        const new_event_top = Math.min(this.newEventInitPosition.y, this.newEventFinalPosition.y);
        const new_event_bot = Math.max(this.newEventInitPosition.y, this.newEventFinalPosition.y);
        const col = this._xToCol(this.newEventInitPosition.x);
        const sd = new Date(this.origin_date.getTime());
        sd.setDate(sd.getDate() + col);
        sd.setHours(this._yToHour(new_event_top));
        sd.setMinutes(this._yToMin(new_event_top));
        if (this.snap_to_grid) { sd.floorN(this.grid_size); }
        const ed = new Date(this.origin_date.getTime());
        ed.setDate(ed.getDate() + col); // TODO: use this.new_event_x_final?
        ed.setHours(this._yToHour(new_event_bot));
        ed.setMinutes(this._yToMin(new_event_bot));
        if (this.snap_to_grid) { ed.ceilN(this.grid_size); }
        this.new_event.start_date = sd;
        this.new_event.end_date = ed;
    }

    _setZoomStartEnd() {
        const zoom_top = Math.min(this.zoomInit.y, this.zoomFinal.y);
        const zoom_bot = Math.max(this.zoomInit.y, this.zoomFinal.y);
        this.zoom_start_hour = this._yToHour(zoom_top);
        //this.zoom_end_hour   = this._yToHour(zoom_bot)+1;
        this.zoom_end_hour   = this._yToHour(zoom_bot);
    }

    setOriginDateFromToday() {
        this.originOffsetFromSunday = DEFAULT_START_DAY;
        this.origin_date = new Date();

        const currTime = new Date();
        if (currTime.getDay() >= DEFAULT_START_DAY) {
            this.origin_date.setDate(this.origin_date.getDate() - currTime.getDay() + DEFAULT_START_DAY);
        } else {
            this.origin_date.setDate(this.origin_date.getDate() - currTime.getDay() + DEFAULT_START_DAY - DAYS_IN_WEEK);
        }

        this.origin_date.setHours(0);
        this.origin_date.setMinutes(0);
        this.origin_date.setSeconds(0);
        this.origin_date.setMilliseconds(0);
        this.next_origin_date = new Date(this.origin_date.getTime());
        this.next_origin_date.setDate(this.origin_date.getDate() + DAYS_IN_WEEK);
    }

    _advanceOriginDate(n) {
        this.origin_date.setDate(this.origin_date.getDate() + n);
        this.next_origin_date.setDate(this.next_origin_date.getDate() + n);
        this.originOffsetFromSunday = inWeek(this.originOffsetFromSunday + n);
    }

    render() {

        ctx.clearRect(0, 0, can_w, can_h);

        ctx.fillStyle = currentTheme.bgColor;
        ctx.fillRect(0, 0, can_w, can_h);

        ctx.lineWidth = 1;
        ctx.fillStyle = currentTheme.fontColor;
        ctx.font = "15px Arial";

        // draw horizontal lines
        for (let i = 0; i < this._hours_in_view(); i++) {

            // draw solid lines
            ctx.strokeStyle = currentTheme.lineColor;
            const hour_y = to_px(this.top_row_px + i*this._hourHeight());
            ctx.beginPath();
            ctx.moveTo(this._getSidebarPx(),  hour_y);
            ctx.lineTo(can_w,                 hour_y);
            ctx.stroke();

            // draw dashed lines
            ctx.strokeStyle = currentTheme.dashedColor;
            ctx.setLineDash([]);
            ctx.setLineDash([3, 1]);
            const grid_per_hour = Math.round(60/this.grid_size); 
            const grid_height = this._hourHeight()/grid_per_hour;
            for (let j = 1; j < grid_per_hour; j++) {
                const grid_y = to_px(hour_y + j*grid_height);
                ctx.beginPath();
                ctx.moveTo(this._getSidebarPx() + this.left_col_px, grid_y);
                ctx.lineTo(can_w,                                   grid_y);
                ctx.stroke();
            }
            ctx.setLineDash([]);

            // write hour on left-hand side
            const hour = i + this.view_start_hour;
            const oclock = militaryTo12hr(hour);
            const am_pm = (hour < 12) ? 'am' : 'pm';
            ctx.fillText(`${oclock} ${am_pm}`, this._getSidebarPx() + 5, this.top_row_px + i*this._hourHeight()+15);
        }

        // draw events
        // TODO: draw events that span multiple days
        ctx.lineWidth = 2;
        for (const e of events) {
            if ((e.end_date >= this.origin_date) && (e.start_date <= this.next_origin_date)) {
                const [dest_start_date, dest_end_date] = this._getModifiedTimes(e);

                const start_col = inWeek(dest_start_date.getDay() - this.originOffsetFromSunday);
                const top_px = this._hoursMinsToY(dest_start_date.getHours(), dest_start_date.getMinutes());
                const bot_px = this._hoursMinsToY(dest_end_date.getHours(),   dest_end_date.getMinutes());

                ctx.fillStyle = e.color;
                const e_color = change_brightness(ctx.fillStyle, currentTheme.eventBrightness);

                const past_color = currentTheme.pastColor;

                ctx.fillStyle = (e.end_date.getTime() < Date.now()) ? past_color : e_color;
                ctx.fillStyle = (e === this.selected_event) ? "cyan" : ctx.fillStyle;
                ctx.strokeStyle = change_brightness(ctx.fillStyle, 50);
                ctx.roundRect(
                    this._getSidebarPx() + this.left_col_px + start_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1,
                    top_px,
                    0.9*this._colWidth() - e.layer*this._colWidth()*0.1,
                    bot_px - top_px,
                    8,
                    true, true
                );

                const hoursLong = (dest_end_date.getTime() - dest_start_date.getTime())/(1000*60*60);
                const pxTall = hoursLong * this._hourHeight();
                const tallerThan40 = pxTall > 40;

                ctx.fillStyle = currentTheme.fontColor;
                let font_px;
                if (tallerThan40) {
                    font_px = 20;
                } else {
                    font_px = 14;
                }
                ctx.font = `bold ${font_px}px Arial`;
                const titlePosition = new Point();
                titlePosition.x = this._getSidebarPx() + this.left_col_px + (start_col+0.1)*this._colWidth() + e.layer*this._colWidth()*0.1;
                titlePosition.y = (top_px+bot_px)/2;

                titlePosition.y = Math.max(titlePosition.y, this.top_row_px+20);
                titlePosition.y = Math.min(titlePosition.y, bot_px - 20);

                titlePosition.y = Math.min(titlePosition.y, can_h - 20);
                titlePosition.y = Math.max(titlePosition.y, top_px + font_px);
                ctx.fillText(e.title, titlePosition.x, titlePosition.y);

                ctx.font = "18px Arial";
                const startEndStr = getStartAndEndTimeString(dest_start_date, dest_end_date);
                const startEndStrPosition = new Point();
                startEndStrPosition.x = this._getSidebarPx() + this.left_col_px + (start_col+0.1)*this._colWidth() + e.layer*this._colWidth()*0.1;
                //startEndStrPosition.y = (top_px+bot_px)/2 + 20;
                startEndStrPosition.y = titlePosition.y + 20;
                if (tallerThan40) {
                    ctx.fillText(startEndStr, startEndStrPosition.x, startEndStrPosition.y);
                }
            }
        }

        // draw new event if active
        ctx.fillStyle = "red";
        if (this.new_event_active) {
            const col = this._xToCol(this.newEventInitPosition.x);
            const top_px = this._hoursMinsToY(this.new_event.start_date.getHours(), this.new_event.start_date.getMinutes());
            const bot_px = this._hoursMinsToY(this.new_event.end_date.getHours(), this.new_event.end_date.getMinutes());
            //ctx.fillRect(this.left_col_px + col*this._colWidth()+1, top_px, this._colWidth()-1, bot_px - top_px);
            ctx.roundRect(
                this._getSidebarPx() + this.left_col_px + col*this._colWidth()+1,
                top_px,
                this._colWidth()-1,
                bot_px - top_px,
                8,
                true,
                true
            );
        }

        // draw vertical lines and dates across top
        ctx.fillStyle = currentTheme.bgColor;
        ctx.fillRect(0, 0, can_w, this.top_row_px-1); // white box on top of events

        ctx.lineWidth = 3;
        ctx.strokeStyle = currentTheme.lineColor;
        ctx.beginPath();
        ctx.moveTo(0, to_px(this.top_row_px));
        ctx.lineTo(can_w, to_px(this.top_row_px));
        ctx.stroke();

        ctx.fillStyle = currentTheme.fontColor;
        ctx.lineWidth = 1;
        ctx.font = "bold 15px Arial";
        for (let i = 0; i < DAYS_IN_WEEK; i++) {
            ctx.beginPath();
            ctx.moveTo(to_px(this._getSidebarPx() + this.left_col_px + i*this._colWidth()), 0);
            ctx.lineTo(to_px(this._getSidebarPx() + this.left_col_px + i*this._colWidth()), can_h);
            ctx.stroke();
            const col_date = new Date(this.origin_date.getTime());
            col_date.setDate(col_date.getDate() + i);
            const month = monthNamesShort[col_date.getMonth()];
            const _date = col_date.getDate();
            const dayName = dayNamesShort[(i + this.originOffsetFromSunday) % DAYS_IN_WEEK];
            ctx.fillText(dayName,               to_px(this._getSidebarPx() + this.left_col_px + 10 + i*this._colWidth()), 20);
            ctx.fillText(`${month} ${_date}`,   to_px(this._getSidebarPx() + this.left_col_px + 10 + i*this._colWidth()), 40);
        }

        // draw current time
        const current_date = new Date();
        const col = inWeek(current_date.getDay() - this.originOffsetFromSunday);
        const line_y = this._hoursMinsToY(current_date.getHours(), current_date.getMinutes());
        ctx.lineWidth = 3;
        ctx.strokeStyle = "red";
        if (current_date.getTime() >= this.origin_date.getTime() && current_date.getTime() <= this.next_origin_date.getTime()) {
            ctx.beginPath();
            ctx.moveTo(to_px(this._getSidebarPx() + this.left_col_px + col*this._colWidth()), to_px(line_y));
            ctx.lineTo(to_px(this._getSidebarPx() + this.left_col_px + (col+1)*this._colWidth()), to_px(line_y));
            ctx.stroke();
        }

        if (this.zoom_active) {
            ctx.fillStyle = currentTheme.zoomColor;
            const zoom_top_px = this._hoursMinsToY(this.zoom_start_hour, 0);
            const zoom_bot_px = this._hoursMinsToY(this.zoom_end_hour+1, 0);
            ctx.fillRect(
                this._getSidebarPx(),
                zoom_top_px,
                can_w - this._getSidebarPx(),
                zoom_bot_px - zoom_top_px
            );
        }

        if (hotkeys_menu) {
            ctx.fillStyle = currentTheme.shadowColor; 
            ctx.fillRect(0, 0, can_w, can_h);
            ctx.lineWidth = 3;
            ctx.font = "bold 15px Arial";
            ctx.fillStyle = currentTheme.bgColor;
            ctx.roundRect(can_w/3, can_h/3, can_w/3, can_h/3, 8, true, false);
            ctx.fillStyle = currentTheme.fontColor;
            ctx.fillText("Hotkeys", can_w/3+15, can_h/3+30);
        }

        //window.requestAnimationFrame(draw);

    }

    keydown(e) {
        if (e.key == "n") {
            this._advanceOriginDate(DAYS_IN_WEEK);
            MyThymeAPI.getEvents();
        } else if (e.key == "p") {
            this._advanceOriginDate(-DAYS_IN_WEEK);
            MyThymeAPI.getEvents();
        } else if (e.key == "t") {
            this.setOriginDateFromToday();
            MyThymeAPI.getEvents();
        } else if (e.key == "Escape") {
            this.selected_event = null;
            hotkeys_menu = false;

        } else if (e.key == "Delete") {
            if (this.selected_event !== null) {
                MyThymeAPI.deleteEvent(this.selected_event.id);
            }

        } else if (e.key == "h") {
            this._advanceOriginDate(-1);
            // TODO: getEvents() efficiently
        } else if (e.key == "l") {
            this._advanceOriginDate(1);
            // TODO: getEvents() efficiently

        } else if (e.key == "-") {
            if (this.grid_idx > 0) { this.grid_idx--; }
            this.grid_size = grid_presets[this.grid_idx];
        } else if (e.key == "+") {
            if (this.grid_idx < grid_presets.length-1) { this.grid_idx++; }
            this.grid_size = grid_presets[this.grid_idx];

        } else if (e.key == "j") {
            if (this.view_end_hour < HOURS_IN_DAY) {
                this.view_start_hour += 1;
                this.view_end_hour += 1;
            }
        } else if (e.key == "k") {
            if (this.view_start_hour > 0) {
                this.view_start_hour -= 1;
                this.view_end_hour -= 1;
            }

        } else if (e.key == "i") {
            if (this.view_start_hour < this.view_end_hour - 2) {
                this.view_start_hour += 1;
                this.view_end_hour -= 1;
            }
        } else if (e.key == "o") {
            if (this.view_start_hour > 0) {
                this.view_start_hour -= 1;
            }
            if (this.view_end_hour < HOURS_IN_DAY) {
                this.view_end_hour += 1;
            }

        } else if (e.key == ",") {
            if (this.selected_event === null) {
                this._selectCurrentOrUpcomingEvent();
            } else {
                this._selectPrevEvent();
            }
        } else if (e.key == ".") {
            if (this.selected_event === null) {
                this._selectCurrentOrUpcomingEvent();
            } else {
                this._selectNextEvent();
            }

        } else if (e.key == "S") {
            this.sidebarVisible = !this.sidebarVisible;


        } else if (e.key == "f") {
            this.new_event_active = false;
            this.selected_event = null;
            if (this.view_start_hour == 0) {
                this.view_start_hour = 7;
                this.view_end_hour = 22;
            } else if (this.view_start_hour == 7) {
                this.view_start_hour = 16;
                this.view_end_hour = 21;
            } else {
                this.view_start_hour = 0;
                this.view_end_hour = 23;
            }
            resize(); // NOTE: this calls the global resize function

        } else if (e.key == "ArrowDown" || e.key == "ArrowUp") {
            if (this.selected_event !== null) {
                const dest_end_date = this.selected_event.end_date;
                const dest_start_date = this.selected_event.start_date;
                if (e.shiftKey) {
                    if (e.key == "ArrowDown") {
                        dest_end_date.setMinutes(dest_end_date.getMinutes()+this.grid_size);
                    } else {
                        dest_end_date.setMinutes(dest_end_date.getMinutes()-this.grid_size);
                    }
                } else {
                    if (e.key == "ArrowDown") {
                        dest_start_date.setMinutes(dest_start_date.getMinutes()+this.grid_size);
                        dest_end_date.setMinutes(dest_end_date.getMinutes()+this.grid_size);
                    } else {
                        dest_start_date.setMinutes(dest_start_date.getMinutes()-this.grid_size);
                        dest_end_date.setMinutes(dest_end_date.getMinutes()-this.grid_size);
                    }
                }
                MyThymeAPI.modifyEvent(
                    this.selected_event.id,
                    {
                        start_time: dest_start_date.getSQLTime(),
                        end_time: dest_end_date.getSQLTime()
                    }
                );
            }
        } else if (e.key == "ArrowLeft") {
        } else if (e.key == "ArrowRight") {

        } else if (e.key == "s") {
            this.snap_to_grid = !this.snap_to_grid;
        } else if (e.key == "M") {
            console.log(this._mousePosToDateTime());
        }
    }

    mousedown(e) {
        this.mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? true : this.mouse_left_btn_down;
        this.mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? true : this.mouse_right_btn_down;

        const mouseDownPosition = clientToCanvas(e.clientX, e.clientY);

        if (this.mouse_left_btn_down) {
            this._setClickedEvent(mouseDownPosition.x, mouseDownPosition.y);
            if (this.selected_event === null) {
                if (
                    mouseDownPosition.x > this._getSidebarPx() + this.left_col_px &&
                    mouseDownPosition.y > this.top_row_px
                ) {
                    this.new_event_active = true;
                    this.newEventInitPosition = Object.assign({}, mouseDownPosition);
                    this.newEventFinalPosition = Object.assign({}, mouseDownPosition);
                    this._setNewEventStartEnd()
                } else if (
                    mouseDownPosition.x > this._getSidebarPx() &&
                    mouseDownPosition.x < this._getSidebarPx() + this.left_col_px &&
                    mouseDownPosition.y > this.top_row_px
                ) {
                    this.zoom_active  = true;
                    this.zoomInit = Object.assign({}, mouseDownPosition);
                    this.zoomFinal = Object.assign({}, mouseDownPosition);
                    this._setZoomStartEnd();
                }
            } else {
                this.selectedEventInit = Object.assign({}, mouseDownPosition);
                this.selectedEventFinal = Object.assign({}, mouseDownPosition);
            }
        } else if (this.mouse_right_btn_down) {
            this._setClickedEvent(mouseDownPosition.x, mouseDownPosition.y);
            if (this.selected_event !== null) {
                const new_title = prompt("Rename event", "");
                if (new_title !== null) {
                    this.selected_event.title = new_title;
                    MyThymeAPI.modifyEvent(this.selected_event.id, {title: new_title});
                }
            }
        }
    }

    mouseup(e) {
        this.mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? false : this.mouse_left_btn_down;
        this.mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? false : this.mouse_right_btn_down;

        if (e.button == LEFT_MOUSE_BUTTON) {
            if (this.new_event_active) {
                const dy = this.newEventFinalPosition.y - this.newEventInitPosition.y;
                if (Math.abs(dy) > MIN_DY) {
                    const title = prompt("Enter a title for the event", "New Event");
                    if (title !== null) {
                        // TODO: add the event to events[] instead of using getEvents()
                        MyThymeAPI.createEvent(title, "", "",
                            this.new_event.start_date.getSQLDate(),
                            this.new_event.start_date.getSQLTime(),
                            this.new_event.end_date.getSQLDate(),
                            this.new_event.end_date.getSQLTime());
                    }
                }
                this.new_event_active = false;
                this._calcEventLayers();
            } else if (this.zoom_active) {
                this.view_start_hour = this.zoom_start_hour;
                this.view_end_hour = this.zoom_end_hour;
                this.zoom_active = false;
            } else if (this.selected_event !== null) {
                const [dest_start_date, dest_end_date] = this._getModifiedTimes(this.selected_event);
                if (dest_start_date.getTime() !== this.selected_event.start_date.getTime() ||
                    dest_end_date.getTime() !== this.selected_event.end_date.getTime()) {
                    MyThymeAPI.modifyEvent(this.selected_event.id, {
                        start_date: dest_end_date.getSQLDate(),
                        start_time: dest_start_date.getSQLTime(),
                        end_date: dest_end_date.getSQLDate(),
                        end_time: dest_end_date.getSQLTime(),
                    });

                    // actually move the event instead of displaying it offset
                    this.selected_event.start_date.setTime(dest_start_date.getTime());
                    this.selected_event.end_date.setTime(dest_end_date.getTime());
                    this.selectedEventFinal.x = this.selectedEventInit.x;
                    this.selectedEventFinal.y = this.selectedEventInit.y;
                    this._calcEventLayers();
                }
            }
            draw();
        } else if (e.button == RIGHT_MOUSE_BUTTON) {
        }
    }

    mousemove(e) {
        this.mousePosition = clientToCanvas(e.clientX, e.clientY);

        if (this.mouse_left_btn_down) {
            if (this.new_event_active) {
                this.newEventFinalPosition = Object.assign({}, this.mousePosition);
                this._setNewEventStartEnd();
            } else if (this.zoom_active) {
                this.zoomFinal = Object.assign({}, this.mousePosition);
                this._setZoomStartEnd();
            } else if (this.selected_event !== null) {
                this.selected_event_moved = true;
                this.selectedEventFinal = Object.assign({}, this.mousePosition);
            } else {
            }
            draw();
            // TODO: consider invoking _calcEventLayers() here
        } else {
            const hoverInfo = this._checkForEventHover(this.mousePosition.x, this.mousePosition.y);
            if (hoverInfo.top) {
                canvas.style.cursor = 'n-resize';
            } else if (hoverInfo.bottom) {
                canvas.style.cursor = 'n-resize';
            } else {
                canvas.style.cursor = 'default';
            }
        }
    }

    resize() {
        this.top_row_px = INIT_TOP_ROW_PX;
        this.top_row_px += (can_h-this.top_row_px) - this._hourHeight()*this._hours_in_view();
        this.left_col_px = INIT_LEFT_COL_PX;
        this.left_col_px += (can_w-this.left_col_px-this._getSidebarPx()) - this._colWidth()*DAYS_IN_WEEK;
    }

}

class MonthView extends CalendarView {
    constructor() {
        super();
    }

    render() {
        ctx.clearRect(0, 0, can_w, can_h);

        const now = new Date();

        const firstOfMonth = new Date();
        firstOfMonth.setDate(1);
        firstOfMonth.setHours(0);
        firstOfMonth.setMinutes(0);
        firstOfMonth.setSeconds(0);
        firstOfMonth.setMilliseconds(0);

        const cellDate = new Date(firstOfMonth.getTime());
        cellDate.setDate(cellDate.getDate() - cellDate.getDay());

        // draw day numbers
        // TODO: make this code less utterly repulsive
        ctx.font = "20px Arial";
        for (let i = 0; i < 6; i++) {
            for (let j = 0; j < DAYS_IN_WEEK; j++) {
                const cellRect = new Rect();
                cellRect.x = to_px(can_w/DAYS_IN_WEEK * j);
                cellRect.y = to_px(can_h/6 * i);
                cellRect.w = to_px(can_w/DAYS_IN_WEEK);
                cellRect.h = to_px(can_h/6);

                cellDate.setDate(cellDate.getDate() + 1);
                ctx.fillStyle = currentTheme.dashedColor;
                if (cellDate.getTime() < now.getTime()) {
                    ctx.fillRect(cellRect.x, cellRect.y, cellRect.w, cellRect.h);
                }
                cellDate.setDate(cellDate.getDate() - 1);

                ctx.fillStyle = currentTheme.fontColor;
                ctx.fillText(`${cellDate.getDate()}`, cellRect.x+2, cellRect.y+22);

                cellDate.setDate(cellDate.getDate() + 1);
            }
        }

        ctx.strokeStyle = currentTheme.lineColor;
        ctx.lineWidth = 1;

        // draw vertical lines
        for (let i = 0; i < DAYS_IN_WEEK; i++) {
            ctx.beginPath();
            const x = to_px(can_w/DAYS_IN_WEEK * i);
            ctx.moveTo(x, 0);
            ctx.lineTo(x, can_h);
            ctx.stroke();
        }
        // draw horizontal lines
        for (let i = 0; i < 6; i++) {
            ctx.beginPath();
            const y = to_px(can_h/6 * i);
            ctx.moveTo(0,       y);
            ctx.lineTo(can_w,   y);
            ctx.stroke();
        }
    }

    keydown(e) {
    }

    mousedown(e) {
    }

    mouseup(e) {
    }

    mousemove(e) {
    }

    resize(e) {
    }
}

class QuarterView extends CalendarView {
    constructor() {
        super();
    }

    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }

    keydown(e) {
    }

    mousedown(e) {
    }

    mouseup(e) {
    }

    mousemove(e) {
    }

    resize(e) {
    }
}

class YearView extends CalendarView {
    constructor() {
        super();
    }

    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }

    keydown(e) {
    }

    mousedown(e) {
    }

    mouseup(e) {
    }

    mousemove(e) {
    }

    resize(e) {
    }
}

const dayView = new DayView();
const weekView = new WeekView();
const monthView = new MonthView();
const quarterView = new QuarterView();
const yearView = new YearView();
