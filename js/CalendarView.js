"use strict";

class Rect {
    constructor(x, y, w, h) {
        this.x = x;
        this.y = y;
        this.w = w;
        this.h = h;
    }
}

class RenderableEvent {

    constructor() {
        this.rectangles = [];
    }

    reset() {
        this.rectangles = [];
    }

    addRect(r) {
        this.rectangles.push(r);
    }
}


// What should this class implement? Probably a render() method. And a way to
// convert between <mouse position> and <date & time>. 
//
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

        this.origin_date;
        this.next_origin_date;

        this.mouse_x, this.mouse_y;
        this.mouse_down_x, this.mouse_down_y;

        this.mouse_left_btn_down = false;
        this.mouse_right_btn_down = false;

        this.selected_event = null;
        this.selected_top = false;
        this.selected_bot = false;
        this.selected_event_moved = false;
        this.selected_event_x_init, this.selected_event_y_init;
        this.selected_event_x_final, this.selected_event_y_final;

        this.new_event_active = false;
        this.new_event = {};
        this.new_event_x_init, this.new_event_y_init;
        this.new_event_x_final, this.new_event_y_final;

        this.zoom_active = false;
        this.zoom_x_init,  this.zoom_y_init;
        this.zoom_x_final, this.zoom_y_final;
        this.zoom_start_hour, this.zoom_end_hour;
    }

    _hours_in_view()        { return (this.view_end_hour - this.view_start_hour) + 1; }
    _colWidth()             { return Math.floor((can_w-this.left_col_px)/DAYS_IN_WEEK); }
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
    _xToCol(x) { return Math.floor((x-this.left_col_px)/this._colWidth()); }

    _xyToDateTime(x, y) {
        const col = this._xToCol(x);
        const _date = this._colToDate(col);
        _date.setHours(this._yToHour(y));
        _date.setMinutes(this._yToMin(y));
        _date.setSeconds(0);
        _date.setMilliseconds(0);
        return _date;
    }

    _mousePosToDateTime() { return this._xyToDateTime(this.mouse_x, this.mouse_y); }

    // TODO: this code is redundant with _setClickedEvent, eliminate redundancy
    _checkForEventHover(x, y) {
        let hoverInfo = {};
        hoverInfo.e = null;
        for (const e of events) {
            // skip checking events that are not in view
            if ((e.end_date < this.origin_date) || (e.start_date > this.next_origin_date)) {
                continue;
            }
            const e_col = e.start_date.getDay();
            const e_x_min = this.left_col_px + e_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1;
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
            const e_col = e.start_date.getDay();
            const e_x_min = this.left_col_px + e_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1;
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
        for (let d = 0; d < DAYS_IN_WEEK; d++) { // day in week

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
                        if ((e2.layer === e.layer) && (e2.start_date.getTime() < e.start_date.getTime())) {
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
    // TODO: allow for moving across columns
    _getModifiedTimes(e) {
        const dx = (e === this.selected_event && this.selected_event_moved) ? this.selected_event_x_final - this.selected_event_x_init : 0;
        const dy = (e === this.selected_event && this.selected_event_moved) ? this.selected_event_y_final - this.selected_event_y_init : 0;
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
            const init_col = this._xToCol(this.selected_event_x_init);
            const final_col = this._xToCol(this.selected_event_x_final);
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
        const new_event_top = Math.min(this.new_event_y_init, this.new_event_y_final);
        const new_event_bot = Math.max(this.new_event_y_init, this.new_event_y_final);
        const col = this._xToCol(this.new_event_x_init);
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
        const zoom_top = Math.min(zoom_y_init, zoom_y_final);
        const zoom_bot = Math.max(zoom_y_init, zoom_y_final);
        this.zoom_start_hour = this._yToHour(zoom_top);
        //this.zoom_end_hour   = this._yToHour(zoom_bot)+1;
        this.zoom_end_hour   = this._yToHour(zoom_bot);
    }

    setOriginDateFromToday() {
        this.origin_date = new Date()
        this.origin_date.setDate((new Date()).getDate() - (new Date()).getDay());
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
            ctx.moveTo(0,     hour_y);
            ctx.lineTo(can_w, hour_y);
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
                ctx.moveTo(this.left_col_px, grid_y);
                ctx.lineTo(can_w, grid_y);
                ctx.stroke();
            }
            ctx.setLineDash([]);

            // write hour on left-hand side
            const hour = i + this.view_start_hour;
            const oclock = militaryTo12hr(hour);
            const am_pm = (hour < 12) ? 'am' : 'pm';
            ctx.fillText(`${oclock} ${am_pm}`, 5, this.top_row_px + i*this._hourHeight()+15);
        }

        // draw events
        // TODO: draw events that span multiple days
        ctx.lineWidth = 2;
        for (const e of events) {
            if ((e.end_date >= this.origin_date) && (e.start_date <= this.next_origin_date)) {
                const [dest_start_date, dest_end_date] = this._getModifiedTimes(e);

                //const start_col = e.start_date.getDay();
                //const end_col = e.end_date.getDay();
                const start_col = dest_start_date.getDay();
                const top_px = this._hoursMinsToY(dest_start_date.getHours(), dest_start_date.getMinutes());
                const bot_px = this._hoursMinsToY(dest_end_date.getHours(),   dest_end_date.getMinutes());

                ctx.fillStyle = e.color;
                const e_color = change_brightness(ctx.fillStyle, currentTheme.eventBrightness);

                const past_color = currentTheme.pastColor;

                ctx.fillStyle = (e.end_date.getTime() < Date.now()) ? past_color : e_color;
                ctx.fillStyle = (e === this.selected_event) ? "cyan" : ctx.fillStyle;
                ctx.strokeStyle = change_brightness(ctx.fillStyle, 50);
                ctx.roundRect(
                    this.left_col_px + start_col*this._colWidth()+1 + e.layer*this._colWidth()*0.1,
                    top_px,
                    0.9*this._colWidth() - e.layer*this._colWidth()*0.1,
                    bot_px - top_px, 8,
                    true, true
                );

                const longerThan15Min = dest_end_date.getTime() - dest_start_date.getTime() > 1000*60*30;

                ctx.fillStyle = currentTheme.fontColor;
                let px_offset;
                if (longerThan15Min) {
                    ctx.font = "bold 20px Arial";
                    px_offset = 0;
                } else {
                    ctx.font = "bold 14px Arial";
                    px_offset = 6;
                }
                ctx.fillText(e.title, this.left_col_px + (start_col+0.1)*this._colWidth(), (top_px+bot_px)/2 + px_offset);
                ctx.font = "18px Arial";
                const startEndStr = getStartAndEndTimeString(dest_start_date, dest_end_date);
                if (longerThan15Min) {
                    ctx.fillText(startEndStr, this.left_col_px+(start_col+0.1)*this._colWidth(), (top_px+bot_px)/2 + 20);
                }
            }
        }

        // draw new event if active
        ctx.fillStyle = "red";
        if (this.new_event_active) {
            const col = this._xToCol(this.new_event_x_init);
            const top_px = this._hoursMinsToY(this.new_event.start_date.getHours(), this.new_event.start_date.getMinutes());
            const bot_px = this._hoursMinsToY(this.new_event.end_date.getHours(), this.new_event.end_date.getMinutes());
            ctx.fillRect(this.left_col_px + col*this._colWidth()+1, top_px, this._colWidth()-1, bot_px - top_px);
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
            ctx.moveTo(to_px(this.left_col_px + i*this._colWidth()), 0);
            ctx.lineTo(to_px(this.left_col_px + i*this._colWidth()), can_h);
            ctx.stroke();
            const col_date = new Date(this.origin_date.getTime());
            col_date.setDate(col_date.getDate() + i);
            const month = monthNamesShort[col_date.getMonth()];
            const _date = col_date.getDate();
            ctx.fillText(dayNamesShort[i],    to_px(this.left_col_px + 10 + i*this._colWidth()), 20);
            ctx.fillText(`${month} ${_date}`, to_px(this.left_col_px + 10 + i*this._colWidth()), 40);
        }

        // draw current time
        const current_date = new Date();
        const col = current_date.getDay();
        const line_y = this._hoursMinsToY(current_date.getHours(), current_date.getMinutes());
        ctx.lineWidth = 3;
        ctx.strokeStyle = "red";
        if (current_date.getTime() >= this.origin_date.getTime() && current_date.getTime() <= this.next_origin_date.getTime()) {
            ctx.beginPath();
            ctx.moveTo(to_px(this.left_col_px + col*this._colWidth()), to_px(line_y));
            ctx.lineTo(to_px(this.left_col_px + (col+1)*this._colWidth()), to_px(line_y));
            ctx.stroke();
        }

        if (this.zoom_active) {
            ctx.fillStyle = currentTheme.zoomColor;
            const zoom_top_px = this._hoursMinsToY(this.zoom_start_hour, 0);
            const zoom_bot_px = this._hoursMinsToY(this.zoom_end_hour+1, 0);
            ctx.fillRect(0, zoom_top_px, this.left_col_px, zoom_bot_px - zoom_top_px);
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
            this._advanceOriginDate(7);
            MyThymeAPI.getEvents();
        } else if (e.key == "p") {
            this._advanceOriginDate(-7);
            MyThymeAPI.getEvents();
        } else if (e.key == "t") {
            setOriginDateFromToday();
            MyThymeAPI.getEvents();
        } else if (e.key == "Escape") {
            this.selected_event = null;
            hotkeys_menu = false;

        } else if (e.key == "Delete") {
            if (this.selected_event !== null) {
                MyThymeAPI.deleteEvent(this.selected_event.id);
            }

        } else if (e.key == "[") {
            if (this.grid_idx > 0) { this.grid_idx--; }
            this.grid_size = grid_presets[this.grid_idx];
        } else if (e.key == "]") {
            if (this.grid_idx < grid_presets.length-1) { this.grid_idx++; }
            this.grid_size = grid_presets[this.grid_idx];

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
            resize();

        } else if (e.key == "ArrowDown") {
            if (this.selected_event !== null) {
                const dest_start_date = this.selected_event.start_date;
                const dest_end_date = this.selected_event.end_date;
                dest_start_date.setMinutes(dest_start_date.getMinutes()+this.grid_size);
                dest_end_date.setMinutes(dest_end_date.getMinutes()+this.grid_size);
                MyThymeAPI.modifyEvent(this.selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
            }
        } else if (e.key == "ArrowUp") {
            if (this.selected_event !== null) {
                const dest_start_date = this.selected_event.start_date;
                const dest_end_date = this.selected_event.end_date;
                dest_start_date.setMinutes(dest_start_date.getMinutes()-this.grid_size);
                dest_end_date.setMinutes(dest_end_date.getMinutes()-this.grid_size);
                MyThymeAPI.modifyEvent(this.selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
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

        this.mouse_down_x = clientToCanvasX(e.clientX);
        this.mouse_down_y = clientToCanvasY(e.clientY);

        if (this.mouse_left_btn_down) {
            this._setClickedEvent(this.mouse_down_x, this.mouse_down_y);
            if (this.selected_event === null) {
                if (this.mouse_down_x > this.left_col_px && this.mouse_down_y > this.top_row_px) {
                    this.new_event_active = true;
                    this.new_event_x_init = this.mouse_down_x;
                    this.new_event_y_init = this.mouse_down_y;
                    this.new_event_x_final = this.mouse_down_x;
                    this.new_event_y_final = this.mouse_down_y;
                    this._setNewEventStartEnd()
                } else if (this.mouse_down_x < this.left_col_px && this.mouse_down_y > this.top_row_px) {
                    this.zoom_active  = true;
                    this.zoom_x_init  = this.mouse_down_x;
                    this.zoom_y_init  = this.mouse_down_y;
                    this.zoom_x_final = this.mouse_down_x;
                    this.zoom_y_final = this.mouse_down_y;
                    this._setZoomStartEnd();
                }
            } else {
                this.selected_event_x_init = this.mouse_down_x;
                this.selected_event_y_init = this.mouse_down_y;
                this.selected_event_x_final = this.mouse_down_x;
                this.selected_event_y_final = this.mouse_down_y;
            }
        } else if (this.mouse_right_btn_down) {
            this._setClickedEvent(this.mouse_down_x, this.mouse_down_y);
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
                const dy = this.new_event_y_final - this.new_event_y_init;
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
                    this.selected_event_x_final = this.selected_event_x_init;
                    this.selected_event_y_final = this.selected_event_y_init;
                    this._calcEventLayers();
                }
            }
            draw();
        } else if (e.button == RIGHT_MOUSE_BUTTON) {
        }
    }

    mousemove(e) {
        this.mouse_x = clientToCanvasX(e.clientX);
        this.mouse_y = clientToCanvasY(e.clientY);

        if (this.mouse_left_btn_down) {
            if (this.new_event_active) {
                this.new_event_x_final = this.mouse_x;
                this.new_event_y_final = this.mouse_y;
                this._setNewEventStartEnd();
            } else if (this.zoom_active) {
                this.zoom_x_final = this.mouse_x;
                this.zoom_y_final = this.mouse_y;
                this._setZoomStartEnd();
            } else if (this.selected_event_moved !== null) {
                this.selected_event_moved = true;
                this.selected_event_x_final = this.mouse_x;
                this.selected_event_y_final = this.mouse_y;
            } else {
            }
            draw();
        } else {
            const hoverInfo = this._checkForEventHover(this.mouse_x, this.mouse_y);
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
        this.left_col_px += (can_w-this.left_col_px) - this._colWidth()*DAYS_IN_WEEK;
    }

}

class MonthView extends CalendarView {
    constructor() {
        super();
    }
    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }
}

class QuarterView extends CalendarView {
    constructor() {
        super();
    }
    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }
}

class YearView extends CalendarView {
    constructor() {
        super();
    }
    render() {
        ctx.clearRect(0, 0, can_w, can_h);
    }
}

const dayView = new DayView();
//Object.freeze(dayView);

const weekView = new WeekView();
//Object.freeze(weekView);

const monthView = new MonthView();
//Object.freeze(monthView);

const quarterView = new QuarterView();
//Object.freeze(quarterView);

const yearView = new YearView();
//Object.freeze(yearView);

//export default instance;

//const required = function(){ throw new Error("Implement!"); };

//const InputInterface = {
//    render: required,
//    value: required
//};

//function Input(){}
//Input.prototype = Object.create(InputInterface);
