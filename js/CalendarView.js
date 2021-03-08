"use strict";

let view_start_hour = 7;
let view_end_hour = 22;
const hours_in_view = () => (view_end_hour - view_start_hour) + 1;

const colWidth = () => Math.floor((can_w-left_col_px)/DAYS_IN_WEEK);
const hourHeight = () => Math.floor((can_h-top_row_px)/hours_in_view());
const hoursMinsToY = (hour, min) => Math.round(top_row_px + ((hour-view_start_hour)+min/60.0) * hourHeight());

const dyTodt = (dy) => {
    const hours = (dy < 0 ? -1 : 1)*Math.floor(Math.abs(dy)/hourHeight());
    const minutes = (dy < 0 ? -1 : 1)*Math.round(60*(Math.abs(dy) % hourHeight())/hourHeight());
    return {hours, minutes};
};

const yToHour = y => Math.floor((y - top_row_px)/hourHeight()) + view_start_hour;
const yToMin = y => Math.round(60*((y - top_row_px) % hourHeight())/hourHeight());

const colToDate = (col) => {
    const d = new Date(origin_date.getTime());
    d.setDate(d.getDate() + col);
    return d;
};

// TODO: handle x - left_col_px < 0 case
const xToCol = x => Math.floor((x-left_col_px)/colWidth());

const xyToDateTime = (x, y) => {
    const col = xToCol(x);
    const _date = colToDate(col);
    _date.setHours(yToHour(y));
    _date.setMinutes(yToMin(y));
    _date.setSeconds(0);
    _date.setMilliseconds(0);
    return _date;
};

const mousePosToDateTime = () => xyToDateTime(mouse_x, mouse_y);

// TODO: this code is redundant with setClickedEvent, eliminate redundancy
function checkForEventHover(x, y) {
    let hoverInfo = {};
    hoverInfo.e = null;
    for (const e of events) {
        // skip checking events that are not in view
        if ((e.end_date < origin_date) || (e.start_date > next_origin_date)) {
            continue;
        }
        const e_col = e.start_date.getDay();
        const e_x_min = left_col_px + e_col*colWidth()+1 + e.layer*colWidth()*0.1;
        const e_x_max = e_x_min + 0.9*colWidth() - e.layer*colWidth()*0.1;
        const e_y_min = Math.max(hoursMinsToY(e.start_date.getHours(), e.start_date.getMinutes()), top_row_px);
        const e_y_max = hoursMinsToY(e.end_date.getHours(), e.end_date.getMinutes());

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
function setClickedEvent(x, y) {
    selected_event = null;
    selected_event_moved = false;
    selected_top = false;
    selected_bot = false;
    for (const e of events) {
        // skip checking events that are not in view
        if ((e.end_date < origin_date) || (e.start_date > next_origin_date)) {
            continue;
        }
        const e_col = e.start_date.getDay();
        const e_x_min = left_col_px + e_col*colWidth()+1 + e.layer*colWidth()*0.1;
        const e_x_max = e_x_min + 0.9*colWidth() - e.layer*colWidth()*0.1;
        const e_y_min = Math.max(hoursMinsToY(e.start_date.getHours(), e.start_date.getMinutes()), top_row_px);
        const e_y_max = hoursMinsToY(e.end_date.getHours(), e.end_date.getMinutes());

        if ((x >= e_x_min && x <= e_x_max) && (y >= e_y_min && y <= e_y_max)) {
            if ((selected_event === null) || (selected_event.layer < e.layer)) {
                selected_event = e;
                selected_top = (y >= e_y_min && y <= e_y_min + EDGE_SIZE) ? true : false;
                selected_bot = (y <= e_y_max && y >= e_y_max - EDGE_SIZE) ? true : false;
                //break;
            }
        }
    }
}

function checkIfEventsOverlap(e1, e2) {
    if ((e2.start_date.getTime() < e1.end_date.getTime()) && (e1.start_date.getTime() < e2.end_date.getTime())) {
        return true;
    } else {
        return false;
    }
}

function calcEventLayers() {
    events.sort((a,b) => a.layer - b.layer);
    for (let d = 0; d < DAYS_IN_WEEK; d++) { // day in week

        let day_in_week = new Date(origin_date.getTime());
        day_in_week.setDate(origin_date.getDate() + d);
        let next_day_in_week = new Date(origin_date.getTime());
        next_day_in_week.setDate(origin_date.getDate() + d+1);

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
                    if (checkIfEventsOverlap(e, e2)) {
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
function getModifiedTimes(e) {
    const dx = (e === selected_event && selected_event_moved) ? selected_event_x_final - selected_event_x_init : 0;
    const dy = (e === selected_event && selected_event_moved) ? selected_event_y_final - selected_event_y_init : 0;
    const dt = dyTodt(dy);

    const dest_start_date = new Date(e.start_date.getTime());
    if (!selected_bot) {
        dest_start_date.setHours(dest_start_date.getHours() + dt.hours);
        dest_start_date.setMinutes(dest_start_date.getMinutes() + dt.minutes);
    }

    const dest_end_date = new Date(e.end_date.getTime());
    if (!selected_top) {
        dest_end_date.setHours(dest_end_date.getHours() + dt.hours);
        dest_end_date.setMinutes(dest_end_date.getMinutes() + dt.minutes);
    }

    if (e === selected_event && selected_event_moved) {
        const init_col = xToCol(selected_event_x_init);
        const final_col = xToCol(selected_event_x_final);
        const dcol = final_col - init_col;

        if (!selected_bot && !selected_top) {
            dest_start_date.setDate(dest_start_date.getDate() + dcol);
            dest_end_date.setDate(dest_end_date.getDate() + dcol);
        }
    }

    // snap to grid
    if (snap_to_grid && Math.abs(dy) > 0) {
        if (selected_top) {
            dest_start_date.roundN(grid_size);
        } else if (selected_bot) {
            dest_end_date.roundN(grid_size);
        } else {
            const rounded_off = dest_start_date.roundN(grid_size);
            dest_end_date.setMinutes(dest_end_date.getMinutes() + rounded_off);
        }
    }

    return [dest_start_date, dest_end_date];
}

function setNewEventStartEnd() {
    const new_event_top = Math.min(new_event_y_init, new_event_y_final);
    const new_event_bot = Math.max(new_event_y_init, new_event_y_final);
    const col = xToCol(new_event_x_init);
    const sd = new Date(origin_date.getTime());
    sd.setDate(sd.getDate() + col);
    sd.setHours(yToHour(new_event_top));
    sd.setMinutes(yToMin(new_event_top));
    if (snap_to_grid) { sd.floorN(grid_size); }
    const ed = new Date(origin_date.getTime());
    ed.setDate(ed.getDate() + col); // TODO: use new_event_x_final?
    ed.setHours(yToHour(new_event_bot));
    ed.setMinutes(yToMin(new_event_bot));
    if (snap_to_grid) { ed.ceilN(grid_size); }
    new_event.start_date = sd;
    new_event.end_date = ed;
}

function setZoomStartEnd() {
    const zoom_top = Math.min(zoom_y_init, zoom_y_final);
    const zoom_bot = Math.max(zoom_y_init, zoom_y_final);
    zoom_start_hour = yToHour(zoom_top);
    //zoom_end_hour   = yToHour(zoom_bot)+1;
    zoom_end_hour   = yToHour(zoom_bot);
}

function setOriginDateFromToday() {
    origin_date = new Date()
    origin_date.setDate((new Date()).getDate() - (new Date()).getDay());
    origin_date.setHours(0);
    origin_date.setMinutes(0);
    origin_date.setSeconds(0);
    origin_date.setMilliseconds(0);
    next_origin_date = new Date(origin_date.getTime());
    next_origin_date.setDate(origin_date.getDate() + DAYS_IN_WEEK);
}

function advanceOriginDate(n) {
    origin_date.setDate(origin_date.getDate() + n);
    next_origin_date.setDate(next_origin_date.getDate() + n);
}

// What should this class implement? Probably a render() method. And a way to
// convert between <mouse position> and <date & time>. 
//
class CalendarView {
    constructor() {
    }
}

class WeekView extends CalendarView {
    constructor() {
        super();
    }
}

class MonthView extends CalendarView {
    constructor() {
        super();
    }
}

class YearView extends CalendarView {
    constructor() {
        super();
    }
}

//const required = function(){ throw new Error("Implement!"); };

//const InputInterface = {
//    render: required,
//    value: required
//};

//function Input(){}
//Input.prototype = Object.create(InputInterface);
