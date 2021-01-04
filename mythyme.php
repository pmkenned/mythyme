<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?><!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta name="description" content="MyThyme" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>MyThyme</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    </head>

    <body class="mythyme">

        <div class="topDiv">
            <div class="alignRight">
                <span class="loggedInAs">
<?php
    echo 'Logged in as <span class="username">' . $_SESSION['username'] . '</span>';
?>
                </span>
                <a href="settings.php"><img class="icon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAABHNCSVQICAgIfAhkiAAAAAFzUkdCAK7OHOkAAAAEZ0FNQQAAsY8L/GEFAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACX0lEQVQ4T6WUv0tyURjHL2FoDonNRlBRIDi0ORUo9A84FNGW0OAgBDk02Cy5WCA0iTgHNogSItVSiZOD+KMiBM1BIfyFoPJ9ex7PtavvTXh5P3C8z/d7nvM9F+89V8IMKpUKVlZWMD8/j4WFBezv74uZ35kZuLS0BJfLhVKphHw+D0mSEIlExKw6MwMpoFqtCgU4HA4cHx8Lpc44MBwOY29vD9lslrXT6eRAJc/Pz+wVi0XWoVAIh4eHaDabrAlecXd3x41yCA2NRoNOp8NNSoLB4ESP3W7nWoYrMqLRKBvTvL+/4/r6GrFYTDh/Q//1yckJ11IymZzYQQk9YZpbW1vjK41UKiVmf/j4+BhnSP1+n8Xl5SUbMiaTCZubm0KNoDDqzeVywhlBfaurq1xz7OfnJzfe3t6yKb8iavh8PhgMBqEAi8WC5eVloUQgEQgExnd0cXGBra0trtVQbja98dy3wbRaLUmr1XK9uLgo1et1rv8ZSn16euKdXl5eeJfhcMh6MBiwVkLvnljG7OzsQKfTCfV9x19fX9ww/fToyJHfaDSEA6TTafYeHx+FM2J7extGo5FrSW5Sw+Px8Jxy3NzciNkf3t7exhn8S8Lv97NBlMtlUY14eHjgY6ekVquJCtDr9Tg7O+OaA+lrQqFWq3V8JzReX1+5SYnX653oodeGrjLjKpPJ4Pz8HN1ul/XV1dVEI3F/f89eu91mTafs9PQUdDhkJldMQYsLhYJQwMHBAY6OjoRSZ2ag2WzG7u4uEokE4vE4b/BfH9herwebzYb19XVsbGzA7XaLmd8A/gCvYsuBQ1/WDAAAAABJRU5ErkJggg==" alt="Settings" title="Settings" /></a>
                <form action="logout.inc.php" method="POST"><button type="submit" name="logout-submit">Logout</button></form>
            </div>
        </div>

        <canvas id="myCanvas"></canvas>

<script>

"use strict";

/**
 * Draws a rounded rectangle using the current state of the canvas.
 * If you omit the last three params, it will draw a rectangle
 * outline with a 5 pixel border radius
 * @param {CanvasRenderingContext2D} ctx
 * @param {Number} x The top left x coordinate
 * @param {Number} y The top left y coordinate
 * @param {Number} width The width of the rectangle
 * @param {Number} height The height of the rectangle
 * @param {Number} [radius = 5] The corner radius; It can also be an object 
 *                 to specify different radii for corners
 * @param {Number} [radius.tl = 0] Top left
 * @param {Number} [radius.tr = 0] Top right
 * @param {Number} [radius.br = 0] Bottom right
 * @param {Number} [radius.bl = 0] Bottom left
 * @param {Boolean} [fill = false] Whether to fill the rectangle.
 * @param {Boolean} [stroke = true] Whether to stroke the rectangle.
 */
function roundRect(x, y, width, height, radius, fill, stroke) {
    if (typeof stroke === 'undefined') {
        stroke = true;
    }
    if (typeof radius === 'undefined') {
        radius = 5;
    }
    if (typeof radius === 'number') {
        radius = {tl: radius, tr: radius, br: radius, bl: radius};
    } else {
        var defaultRadius = {tl: 0, tr: 0, br: 0, bl: 0};
        for (var side in defaultRadius) {
            radius[side] = radius[side] || defaultRadius[side];
        }
    }
    this.beginPath();
    this.moveTo(x + radius.tl, y);
    this.lineTo(x + width - radius.tr, y);
    this.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
    this.lineTo(x + width, y + height - radius.br);
    this.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
    this.lineTo(x + radius.bl, y + height);
    this.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
    this.lineTo(x, y + radius.tl);
    this.quadraticCurveTo(x, y, x + radius.tl, y);
    this.closePath();
    if (fill) {
        this.fill();
    }
    if (stroke) {
        this.stroke();
    }
}

const MIN_DY = 0;
const EDGE_SIZE = 10;

const LEFT_MOUSE_BUTTON = 0;
const RIGHT_MOUSE_BUTTON = 2;

let view_start_hour = 7;
let view_end_hour = 22;
const hours_in_view = () => (view_end_hour - view_start_hour) + 1;

const HOURS_IN_DAY = 24;
const DAYS_IN_WEEK = 7;

const INIT_TOP_ROW_PX = 50;
const INIT_LEFT_COL_PX = 45;
let top_row_px = INIT_TOP_ROW_PX;
let left_col_px = INIT_LEFT_COL_PX;

const grid_presets = [1, 5, 10, 15, 20, 30, 60];
let grid_idx = 3;
let grid_size = grid_presets[grid_idx];

const dayNamesShort = [
    'Sun',
    'Mon',
    'Tue',
    'Wed',
    'Thu',
    'Fri',
    'Sat'
];

const dayNames = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
];

const monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
];

const monthNamesShort = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec'
];

const colors = [
    'hsl(0,   50%, 70%)',
    'hsl(30,  50%, 70%)',
    'hsl(60,  50%, 70%)',
    'hsl(90,  50%, 70%)',
    'hsl(120, 50%, 70%)',
    'hsl(150, 50%, 70%)',
    'hsl(180, 50%, 70%)',
    'hsl(210, 50%, 70%)',
    'hsl(240, 50%, 70%)',
    'hsl(270, 50%, 70%)',
    'hsl(300, 50%, 70%)',
    'hsl(330, 50%, 70%)',
];

let $canvas;
let canvas;
let ctx;
let can_w, can_h;

let snap_to_grid = true;

let origin_date;
let next_origin_date;

let mouse_x, mouse_y;
let mouse_down_x, mouse_down_y;

let mouse_left_btn_down = false;
let mouse_right_btn_down = false;

let selected_event = null;
let selected_top = false;
let selected_bot = false;
let selected_event_moved = false;
let selected_event_x_init, selected_event_y_init;
let selected_event_x_final, selected_event_y_final;

let new_event_active = false;
let new_event = {};
let new_event_x_init, new_event_y_init;
let new_event_x_final, new_event_y_final;

let zoom_active = false;
let zoom_x_init,  zoom_y_init;
let zoom_x_final, zoom_y_final;
let zoom_start_hour, zoom_end_hour;

let hotkeys_menu = false;

const LIGHT_THEME = 0;
const DARK_THEME = 1;
const DEFAULT_THEME = LIGHT_THEME;
let theme = DEFAULT_THEME;

let events = [];

function getDateFromSQL(sqlDate, sqlTime) {
    let [year, month, _date] = sqlDate.split('-').map(Number);
    month--; // NOTE: JavaScript's Date class indexes months starting at 0
    const [hour, min] = sqlTime.split(':').map(Number);
    return new Date(year, month, _date, hour, min);
}

const clientToCanvasY = y => y - canvas.offsetTop;
const clientToCanvasX = x => x;
const to_px = x => Math.round(x) + 0.5;
const floorToMultiple = (x,n) => Math.floor(x/n)*n;
const ceilToMultiple = (x,n) => Math.ceil(x/n)*n;
const roundToMultiple = (x,n) => Math.round(x/n)*n;
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

const militaryTo12hr = hour => (hour == 0) ? 12 : ((hour > 12) ? hour-12 : hour);

const getStartAndEndTimeString = (sdate, edate) => {
    let start_time = `${militaryTo12hr(sdate.getHours())}`;
    if (sdate.getMinutes() != 0) {
        start_time += `:${sdate.getMinutes().toString().lpad("0",2)}`;
    }
    const s_ampm = (sdate.getHours() < 12) ? 'am' : 'pm';
    let end_time = `${militaryTo12hr(edate.getHours())}`;
    if (edate.getMinutes() != 0) {
        end_time += `:${edate.getMinutes().toString().lpad("0",2)}`;
    }
    const e_ampm = (edate.getHours() < 12) ? 'am' : 'pm';
    return (s_ampm === e_ampm) ? `${start_time} - ${end_time}${e_ampm}` : `${start_time}${s_ampm} - ${end_time}${e_ampm}`;
};

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
const minutesBetweenDates = (dt1, dt2) => Math.round((dt2.getTime() - dt1.getTime()) / (60*1000));

const parseInt16 = s => parseInt(s, 16);
// TODO: use HSL
const change_brightness = (color, percent) => {
    const r = Math.min(Math.round(parseInt16(color.substr(1,2)) * percent/100.0), 255);
    const g = Math.min(Math.round(parseInt16(color.substr(3,2)) * percent/100.0), 255);
    const b = Math.min(Math.round(parseInt16(color.substr(5,2)) * percent/100.0), 255);
    const rgb = r.toString(16).lpad("0", 2) + g.toString(16).lpad("0", 2) + b.toString(16).lpad("0", 2);
    return ("#" + rgb);
};

function draw(timestamp) {
    ctx.clearRect(0, 0, can_w, can_h);

    ctx.fillStyle = (theme == LIGHT_THEME) ? "white" : "hsl(0, 0%, 20%)";
    ctx.fillRect(0, 0, can_w, can_h);

    ctx.lineWidth = 1;
    ctx.fillStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";
    ctx.font = "15px Arial";

    // draw horizontal lines
    for (let i = 0; i < hours_in_view(); i++) {
        ctx.strokeStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";

        // draw solid lines
        const hour_y = to_px(top_row_px + i*hourHeight());
        ctx.beginPath();
        ctx.moveTo(0,     hour_y);
        ctx.lineTo(can_w, hour_y);
        ctx.stroke();

        // draw dashed lines
        ctx.strokeStyle = (theme == LIGHT_THEME) ? "hsl(0, 0%, 70%)" : "hsl(0, 0%, 30%)";
        ctx.setLineDash([]);
        ctx.setLineDash([3, 1]);
        const grid_per_hour = Math.round(60/grid_size); 
        const grid_height = hourHeight()/grid_per_hour;
        for (let j = 1; j < grid_per_hour; j++) {
            const grid_y = to_px(hour_y + j*grid_height);
            ctx.beginPath();
            ctx.moveTo(left_col_px, grid_y);
            ctx.lineTo(can_w, grid_y);
            ctx.stroke();
        }
        ctx.setLineDash([]);

        // write hour on left-hand side
        const hour = i + view_start_hour;
        const oclock = militaryTo12hr(hour);
        const am_pm = (hour < 12) ? 'am' : 'pm';
        ctx.fillText(`${oclock} ${am_pm}`, 5, top_row_px + i*hourHeight()+15);
    }

    // draw events
    // TODO: draw events that span multiple days
    ctx.lineWidth = 2;
    for (const e of events) {
        if ((e.end_date >= origin_date) && (e.start_date <= next_origin_date)) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(e);

            //const start_col = e.start_date.getDay();
            //const end_col = e.end_date.getDay();
            const start_col = dest_start_date.getDay();
            const top_px = hoursMinsToY(dest_start_date.getHours(), dest_start_date.getMinutes());
            const bot_px = hoursMinsToY(dest_end_date.getHours(),   dest_end_date.getMinutes());

            ctx.fillStyle = e.color;
            const e_color = change_brightness(ctx.fillStyle, (theme == LIGHT_THEME) ? 100 : 50);

            const past_color = (theme == LIGHT_THEME) ? "hsl(0, 0%, 80%)" : "hsl(0, 0%, 40%)";

            ctx.fillStyle = (e.end_date.getTime() < Date.now()) ? past_color : e_color;
            ctx.fillStyle = (e === selected_event) ? "cyan" : ctx.fillStyle;
            ctx.strokeStyle = change_brightness(ctx.fillStyle, 50);
            ctx.roundRect(left_col_px + start_col*colWidth()+1, top_px, colWidth()-1, bot_px - top_px, 5, true, true);

            ctx.fillStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";
            ctx.font = "bold 12px Arial";
            ctx.fillText(e.title, left_col_px + (start_col+0.3)*colWidth(), (top_px+bot_px)/2);
            ctx.font = "10px Arial";
            const startEndStr = getStartAndEndTimeString(dest_start_date, dest_end_date);
            ctx.fillText(startEndStr, left_col_px+(start_col+0.3)*colWidth(), (top_px+bot_px)/2 + 14);
        }
    }

    // draw new event if active
    ctx.fillStyle = "red";
    if (new_event_active) {
        const col = xToCol(new_event_x_init);
        const top_px = hoursMinsToY(new_event.start_date.getHours(), new_event.start_date.getMinutes());
        const bot_px = hoursMinsToY(new_event.end_date.getHours(), new_event.end_date.getMinutes());
        ctx.fillRect(left_col_px + col*colWidth()+1, top_px, colWidth()-1, bot_px - top_px);
    }

    // draw vertical lines and dates across top
    ctx.fillStyle = (theme == LIGHT_THEME) ? "white" : "hsl(0, 0%, 20%)";
    ctx.fillRect(0, 0, can_w, top_row_px-1); // white box on top of events

    ctx.lineWidth = 3;
    ctx.strokeStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";
    ctx.beginPath();
    ctx.moveTo(0, to_px(top_row_px));
    ctx.lineTo(can_w, to_px(top_row_px));
    ctx.stroke();

    ctx.fillStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";
    ctx.lineWidth = 1;
    ctx.font = "bold 15px Arial";
    for (let i = 0; i < DAYS_IN_WEEK; i++) {
        ctx.beginPath();
        ctx.moveTo(to_px(left_col_px + i*colWidth()), 0);
        ctx.lineTo(to_px(left_col_px + i*colWidth()), can_h);
        ctx.stroke();
        const col_date = new Date(origin_date.getTime());
        col_date.setDate(col_date.getDate() + i);
        const month = monthNamesShort[col_date.getMonth()];
        const _date = col_date.getDate();
        ctx.fillText(dayNamesShort[i], to_px(left_col_px + i*colWidth()), 20);
        ctx.fillText(`${month} ${_date}`, to_px(left_col_px + i*colWidth()), 40);
    }

    // draw current time
    const current_date = new Date();
    const col = current_date.getDay();
    const line_y = hoursMinsToY(current_date.getHours(), current_date.getMinutes());
    ctx.lineWidth = 3;
    ctx.strokeStyle = "red";
    if (current_date.getTime() >= origin_date.getTime() && current_date.getTime() <= next_origin_date.getTime()) {
        ctx.beginPath();
        ctx.moveTo(to_px(left_col_px + col*colWidth()), to_px(line_y));
        ctx.lineTo(to_px(left_col_px + (col+1)*colWidth()), to_px(line_y));
        ctx.stroke();
    }

    if (zoom_active) {
        ctx.fillStyle = (theme == LIGHT_THEME) ? "hsla(0, 0%, 50%, 0.5)": "hsla(0, 0%, 100%, 0.5)";
        const zoom_top_px = hoursMinsToY(zoom_start_hour, 0);
        const zoom_bot_px = hoursMinsToY(zoom_end_hour, 0);
        ctx.fillRect(0, zoom_top_px, left_col_px, zoom_bot_px - zoom_top_px);
    }

    if (hotkeys_menu) {
        ctx.fillStyle = (theme == LIGHT_THEME) ? "hsla(0, 0%, 0%, 0.5)": "hsla(0, 0%, 100%, 0.5)";
        ctx.fillRect(0, 0, can_w, can_h);
        ctx.lineWidth = 3;
        ctx.font = "bold 15px Arial";
        ctx.fillStyle = (theme == LIGHT_THEME) ? "white" : "hsl(0, 0%, 20%)";
        ctx.roundRect(can_w/3, can_h/3, can_w/3, can_h/3, 5, true, false);
        ctx.fillStyle = (theme == LIGHT_THEME) ? "black" : "hsl(0, 0%, 80%)";
        ctx.fillText("Hotkeys", can_w/3+15, can_h/3+30);
    }

    window.requestAnimationFrame(draw);
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
        const e_x_min = left_col_px + e_col*colWidth()+1;
        const e_x_max = e_x_min + colWidth();
        const e_y_min = hoursMinsToY(e.start_date.getHours(), e.start_date.getMinutes());
        const e_y_max = hoursMinsToY(e.end_date.getHours(), e.end_date.getMinutes());

        if ((x >= e_x_min && x <= e_x_max) && (y >= e_y_min && y <= e_y_max)) {
            selected_event = e;
            selected_top = (y >= e_y_min && y <= e_y_min + EDGE_SIZE) ? true : false;
            selected_bot = (y <= e_y_max && y >= e_y_max - EDGE_SIZE) ? true : false;
            break;
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
    zoom_end_hour   = yToHour(zoom_bot)+1;
}

function mousedown(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? true : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? true : mouse_right_btn_down;

    mouse_down_x = clientToCanvasX(e.clientX);
    mouse_down_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        setClickedEvent(mouse_down_x, mouse_down_y);
        if (selected_event === null) {
            if (mouse_down_x > left_col_px && mouse_down_y > top_row_px) {
                new_event_active = true;
                new_event_x_init = mouse_down_x;
                new_event_y_init = mouse_down_y;
                new_event_x_final = mouse_down_x;
                new_event_y_final = mouse_down_y;
                setNewEventStartEnd()
            } else if (mouse_down_x < left_col_px && mouse_down_y > top_row_px) {
                zoom_active  = true;
                zoom_x_init  = mouse_down_x;
                zoom_y_init  = mouse_down_y;
                zoom_x_final = mouse_down_x;
                zoom_y_final = mouse_down_y;
                setZoomStartEnd();
            }
        } else {
            selected_event_x_init = mouse_down_x;
            selected_event_y_init = mouse_down_y;
            selected_event_x_final = mouse_down_x;
            selected_event_y_final = mouse_down_y;
        }
    } else if (mouse_right_btn_down) {
        setClickedEvent(mouse_down_x, mouse_down_y);
        if (selected_event !== null) {
            const new_title = prompt("Rename event", "");
            if (new_title !== null) {
                selected_event.title = new_title;
                modifyEvent(selected_event.id, {title: new_title});
            }
        }
    }
}

function mouseup(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? false : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? false : mouse_right_btn_down;

    if (e.button == LEFT_MOUSE_BUTTON) {
        if (new_event_active) {
            const dy = new_event_y_final - new_event_y_init;
            if (Math.abs(dy) > MIN_DY) {
                const title = prompt("Enter a title for the event", "New Event");
                if (title !== null) {
                    // TODO: add the event to events[] instead of using getEvents()
                    _createEvent(title, "", "",
                        new_event.start_date.getSQLDate(),
                        new_event.start_date.getSQLTime(),
                        new_event.end_date.getSQLDate(),
                        new_event.end_date.getSQLTime());
                }
            }
            new_event_active = false;
        } else if (zoom_active) {
            view_start_hour = zoom_start_hour;
            view_end_hour = zoom_end_hour;
            zoom_active = false;
        } else if (selected_event !== null) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(selected_event);
            if (dest_start_date.getTime() !== selected_event.start_date.getTime() ||
                dest_end_date.getTime() !== selected_event.end_date.getTime()) {
                modifyEvent(selected_event.id, {
                    start_date: dest_end_date.getSQLDate(),
                    start_time: dest_start_date.getSQLTime(),
                    end_date: dest_end_date.getSQLDate(),
                    end_time: dest_end_date.getSQLTime(),
                });

                // actually move the event instead of displaying it offset
                selected_event.start_date.setTime(dest_start_date.getTime());
                selected_event.end_date.setTime(dest_end_date.getTime());
                selected_event_x_final = selected_event_x_init;
                selected_event_y_final = selected_event_y_init;
            }
        }
    } else if (e.button == RIGHT_MOUSE_BUTTON) {
    }
}

function mousemove(e) {

    mouse_x = clientToCanvasX(e.clientX);
    mouse_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        if (new_event_active) {
            new_event_x_final = mouse_x;
            new_event_y_final = mouse_y;
            setNewEventStartEnd();
        } else if (zoom_active) {
            zoom_x_final = mouse_x;
            zoom_y_final = mouse_y;
            setZoomStartEnd();
        } else if (selected_event_moved !== null) {
            selected_event_moved = true;
            selected_event_x_final = mouse_x;
            selected_event_y_final = mouse_y;
        } else {
        }
    }
}

function keydown(e) {
    if (e.key == "?") {
        hotkeys_menu = !hotkeys_menu;
        //fetch('getErrors.php')
        //  .then(response => response.text())
        //  .then(data => {console.log(data); });
    } else if (e.key == "[") {
        if (grid_idx > 0) { grid_idx--; }
        grid_size = grid_presets[grid_idx];
    } else if (e.key == "]") {
        if (grid_idx < grid_presets.length-1) { grid_idx++; }
        grid_size = grid_presets[grid_idx];
    } else if (e.key == "m") {
        console.log(mousePosToDateTime());
    } else if (e.key == "f") {
        new_event_active = false;
        selected_event = null;
        if (view_start_hour == 0) {
            view_start_hour = 7;
            view_end_hour = 21;
        } else if (view_start_hour == 7) {
            view_start_hour = 16;
            view_end_hour = 21;
        } else {
            view_start_hour = 0;
            view_end_hour = 23;
        }
        resize();
    } else if (e.key == "ArrowDown") {
        if (selected_event !== null) {
            const dest_start_date = selected_event.start_date;
            const dest_end_date = selected_event.end_date;
            dest_start_date.setMinutes(dest_start_date.getMinutes()+grid_size);
            dest_end_date.setMinutes(dest_end_date.getMinutes()+grid_size);
            modifyEvent(selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
        }
    } else if (e.key == "ArrowUp") {
        if (selected_event !== null) {
            const dest_start_date = selected_event.start_date;
            const dest_end_date = selected_event.end_date;
            dest_start_date.setMinutes(dest_start_date.getMinutes()-grid_size);
            dest_end_date.setMinutes(dest_end_date.getMinutes()-grid_size);
            modifyEvent(selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
        }
    } else if (e.key == "ArrowLeft") {
    } else if (e.key == "ArrowRight") {
    } else if (e.key == "s") {
        snap_to_grid = !snap_to_grid;
    } else if (e.key == "d") {
        theme = 1 - theme; // TODO: make this robust
    } else if (e.key == "r") {
        getEvents();
    } else if (e.key == "n") {
        advanceOriginDate(7);
        getEvents();
    } else if (e.key == "p") {
        advanceOriginDate(-7);
        getEvents();
    } else if (e.key == "t") {
        setOriginDateFromToday();
        getEvents();
    } else if (e.key == "Escape") {
        selected_event = null;
        hotkeys_menu = false;
    } else if (e.key == "Delete") {
        if (selected_event !== null) {
            deleteEvent(selected_event.id);
        }
    }
}

function keyup(e) {
}

function blur() {
}

function contextmenu(e) {
    e.preventDefault();
    return false; // 
}

function resize() {
    canvas.width = window.innerWidth - 2;
    canvas.height = window.innerHeight - canvas.offsetTop;
    //canvas.height -= ADDRESS_BAR_HEIGHT; // TODO
    can_w = canvas.width;
    can_h = canvas.height;
    top_row_px = INIT_TOP_ROW_PX;
    top_row_px += (can_h-top_row_px) - hourHeight()*hours_in_view();
    left_col_px = INIT_LEFT_COL_PX;
    left_col_px += (can_w-left_col_px) - colWidth()*DAYS_IN_WEEK;
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

$(function() {

    CanvasRenderingContext2D.prototype.fillStrokeRect = function(x, y, w, h) {
        this.fillRect(x, y, w, h);
        this.strokeRect(x, y, w, h);
    }

    CanvasRenderingContext2D.prototype.roundRect = roundRect;

    String.prototype.sum = function() {
        let sum = 0;
        for (let i = 0; i < this.length; i++) {
            sum += this.charCodeAt(i);
        }
        return sum;
    }

    String.prototype.lpad = function(padString, length) {
        let str = this;
        while (str.length < length) {
            str = padString + str;
        }
        return str;
    }

    Date.prototype.getSQLDate = function() {
        const d = this;
        const month = `${d.getMonth()+1}`.lpad("0", 2);
        const _date = `${d.getDate()}`.lpad("0", 2);
        return `${d.getFullYear()}-${month}-${_date}`;
    }

    Date.prototype.getSQLTime = function() {
        const d = this;
        const hour = `${d.getHours()}`.lpad("0", 2);
        const min = `${d.getMinutes()}`.lpad("0", 2);
        return `${hour}:${min}:00`;
    }

    Date.prototype.floorN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = floorToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

    Date.prototype.ceilN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = ceilToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

    Date.prototype.roundN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = roundToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

    $canvas = $('#myCanvas');
    canvas = $canvas[0];
    ctx = canvas.getContext('2d');
    resize();

    $(window).mousedown(mousedown);
    $(window).mouseup(mouseup);
    $(window).mousemove(mousemove);
    $(window).keydown(keydown);
    $(window).keyup(keyup);
    $(window).blur(blur);
    $(window).contextmenu(contextmenu);
    $(window).resize(resize);

    setOriginDateFromToday();

    draw();

    getEvents();

});

function reloadIfLoggedOut(jqXHR) {
    if (jqXHR.responseJSON === "ERROR: username not defined") {
        console.log('refreshing...');
        window.location.reload();
    }
}

function checkForUpdates() {
    $.get('mt_functions.php', {func: 'checkForUpdates'})
    .done(function(data) {
        console.log(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error(jqXHR.responseJSON);
        reloadIfLoggedOut(jqXHR);
        getEvents();
    });
}

function _createEvent(eventTitle, eventDescription, eventLocation, startDate, startTime, endDate, endTime) {
    $.post('mt_functions.php', {
        func: 'createEvent',
        title: eventTitle,
        desc: eventDescription,
        location: eventLocation,
        start_date: startDate,
        start_time: startTime,
        end_date: endDate,
        end_time: endTime
    })
    .done(function(data) {
        console.log(data);
        getEvents(); // TODO: decide if this is the right way to do this
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error(jqXHR.responseJSON);
        reloadIfLoggedOut(jqXHR);
        getEvents();
    });
}

function getEvents() {
    // TODO: use fetch API
    $.get('mt_functions.php', {
        func: 'getEvents',
        begin_date: origin_date.getSQLDate(),
        end_date: next_origin_date.getSQLDate()
    })
    .done(function(data) {
        for (const e of data) {
            const start_date = getDateFromSQL(e.start_date, e.start_time);
            const end_date = getDateFromSQL(e.end_date, e.end_time);
            const found_event = events.find(item => item.id === e.id);
            //const color = colors[e.id % colors.length];
            const color = colors[e.title.sum() % colors.length];
            if (found_event === undefined) {
                events.push({title: e.title, start_date: start_date, end_date: end_date, color: color, id: e.id });
            } else {
                Object.assign(found_event , {title: e.title, start_date: start_date, end_date: end_date, color: color, id: e.id});
            }
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error(jqXHR.responseJSON);
        reloadIfLoggedOut(jqXHR);
    });
}

function deleteEvent(eventID) {

    // TODO: confirm that no problems are caused by doing this before the POST finishes
    const found_event_idx = events.findIndex(item => item.id === eventID);
    if (found_event_idx === undefined) {
        console.error(`deleteEvent: cannot delete event ${eventID}, no such event`);
        return;
    }
    events.splice(found_event_idx, 1); // delete it

    $.post('mt_functions.php', {
        func: 'deleteEvent',
        event_id: eventID
    })
    .done(function(data) {
        console.log(data);
        //getEvents();
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error(jqXHR.responseJSON);
        reloadIfLoggedOut(jqXHR);
        getEvents();
    });
}

function modifyEvent(eventID, fields) {
    $.post('mt_functions.php', {
        func: 'modifyEvent',
        event_id: eventID,
        ...fields
    }).done(function(data) {
        console.log(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error(jqXHR.responseJSON);
        reloadIfLoggedOut(jqXHR);
        getEvents();
    });
}

//let timer_num = 0;
//
//function test() {
//    foo = {s: "hi", t: 4};
//    bar = {s: "there", t: 5};
//    baz = {s: "you", t: 3};
//    console.log('%c Hello, world', 'color: orange; font-weight: bold;');
//    console.table([foo, bar, baz], ['s','t']);
//    console.dir(foo);
//    console.dir(checkForUpdates);
//    console.trace('my trace');
//
//    console.groupCollapsed();
//        console.warn('this is a warning');
//        console.info('this is info');
//        console.error('this is an error');
//        console.assert(1==2, '1 doesn\'t equal two');
//    console.groupEnd()
//    //console.clear();
//    console.count();
//
//    const timer_str = `${timer_num}`;
//    timer_num++;
//    console.time(timer_str);
//    fetch('mt_functions.php?func=checkForUpdates')
//      .then(response => response.json())
//      .then(data => {console.log(data); console.timeEnd(timer_str); });
//
//    return 'end of test';
//}

</script>

    </body>

</html>
