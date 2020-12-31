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

    <body>

        <div class="topDiv">
            <div class="alignRight">
                <span class="loggedInAs">
<?php
    echo 'Logged in as <span class="username">' . $_SESSION['username'] . '</span>';
?>
                </span>
                <form action="logout.inc.php" method="POST"><button type="submit" name="logout-submit">Logout</button></form>
            </div>
        </div>

        <canvas id="myCanvas"></canvas>

<script>

"use strict";

const MIN_DY = 0;
const EDGE_SIZE = 10;

const LEFT_MOUSE_BUTTON = 0;
const RIGHT_MOUSE_BUTTON = 2;

const HOURS_IN_DAY = 24;
const DAYS_IN_WEEK = 7;

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
let selected_event_x_init, selected_event_y_init;
let selected_event_x_final, selected_event_y_final;

let new_event_active = false;
let new_event = {};
let new_event_x_init, new_event_y_init;
let new_event_x_final, new_event_y_final;

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
const roundToMultiple = (x,n) => Math.round(x/n)*n;
const colWidth = () => Math.round(can_w/DAYS_IN_WEEK);
const hourHeight = () => Math.round(can_h/(HOURS_IN_DAY+1));
const hoursMinsToY = (hour, min) => Math.round((hour+1+min/60.0) * hourHeight());

const dyTodt = (dy) => {
    const d = new Date();
    d.setHours((dy < 0 ? -1 : 1)*Math.floor(Math.abs(dy)/hourHeight()));
    const dm = (dy < 0 ? -1 : 1)*Math.round(60*(Math.abs(dy) % hourHeight())/hourHeight())
    d.setMinutes(dm);
    d.setSeconds(0);
    d.setMilliseconds(0);
    return d;
};

const yToHour = y => Math.floor((y - hourHeight())/hourHeight());
const yToMin = y => Math.round(60*(y % hourHeight())/hourHeight());

const colToDate = (col) => {
    const d = new Date(origin_date.getTime());
    d.setDate(d.getDate() + col);
    return d;
};

const xToCol = x => Math.floor(x / colWidth());

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

function draw(timestamp) {
    ctx.clearRect(0, 0, can_w, can_h);

    ctx.strokeStyle = "black";
    ctx.fillStyle = "black";
    ctx.font = "15px Arial";

    // draw vertical lines
    for (let i = 0; i < DAYS_IN_WEEK; i++) {
        if (true || (i > 0)) {
            ctx.beginPath();
            ctx.moveTo(to_px(i*colWidth()), 0);
            ctx.lineTo(to_px(i*colWidth()), can_h);
            ctx.stroke();
        }
        const col_date = new Date(origin_date.getTime());
        col_date.setDate(col_date.getDate() + i);
        const month = monthNamesShort[col_date.getMonth()];
        const _date = col_date.getDate();
        ctx.fillText(dayNamesShort[i], to_px(i*colWidth()), 20);
        ctx.fillText(`${month}, ${_date}`, to_px(i*colWidth()), 40);
    }

    // draw horizontal lines
    for (let i = 0; i < HOURS_IN_DAY+1; i++) {
        if (i > 0) {
            ctx.fillText(`${i-1}:00`, 5, i*hourHeight()+15);
            ctx.strokeStyle = "black";
            const hour_y = to_px(i*hourHeight());
            ctx.beginPath();
            ctx.moveTo(0,     hour_y);
            ctx.lineTo(can_w, hour_y);
            ctx.stroke();

            ctx.strokeStyle = "hsl(0, 0%, 70%)";
            ctx.setLineDash([]);
            ctx.setLineDash([3, 1]);
            const grid_per_hour = Math.round(60/grid_size); 
            const grid_height = hourHeight()/grid_per_hour;
            for (let j = 1; j < grid_per_hour; j++) {
                const grid_y = to_px(hour_y + j*grid_height);
                ctx.beginPath();
                ctx.moveTo(0,     grid_y);
                ctx.lineTo(can_w, grid_y);
                ctx.stroke();
            }
            ctx.setLineDash([]);
        }
    }

    // draw events
    ctx.font = "10px Arial";
    for (const e of events) {
        if ((e.end_date >= origin_date) && (e.start_date <= next_origin_date)) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(e);

            const col = e.start_date.getDay();
            const top_px = hoursMinsToY(dest_start_date.getHours(), dest_start_date.getMinutes());
            const bot_px = hoursMinsToY(dest_end_date.getHours(),   dest_end_date.getMinutes());

            ctx.fillStyle = (e === selected_event) ? "cyan" : e.color;
            ctx.fillRect(col*colWidth()+1, top_px, colWidth()-1, bot_px - top_px);

            ctx.fillStyle = "white";
            ctx.fillText(e.title, (col+0.3)*colWidth(), (top_px+bot_px)/2);
        }
    }

    // draw new event if active
    ctx.fillStyle = "red";
    if (new_event_active) {
        const new_event_top = Math.min(new_event_y_init, new_event_y_final);
        const new_event_bot = Math.max(new_event_y_init, new_event_y_final);
        const col = xToCol(new_event_x_init);
        const sd = new Date(origin_date.getTime());
        sd.setDate(sd.getDate() + col);
        sd.setHours(yToHour(new_event_top));
        sd.setMinutes(yToMin(new_event_top));
        if (snap_to_grid) { sd.roundN(grid_size); }
        const ed = new Date(origin_date.getTime());
        ed.setDate(ed.getDate() + col); // TODO: use new_event_x_final?
        ed.setHours(yToHour(new_event_bot));
        ed.setMinutes(yToMin(new_event_bot));
        if (snap_to_grid) { ed.roundN(grid_size); }
        new_event.start_date = sd;
        new_event.end_date = ed;
        const top_px = hoursMinsToY(sd.getHours(), sd.getMinutes());
        const bot_px = hoursMinsToY(ed.getHours(), ed.getMinutes());
        ctx.fillRect(col*colWidth()+1, top_px, colWidth()-1, bot_px - top_px);
    }

    window.requestAnimationFrame(draw);
}

// TODO: encapsulate the calculations used here
function setClickedEvent(x, y) {
    selected_event = null;
    selected_top = false;
    selected_bot = false;
    for (const e of events) {
        const e_col = e.start_date.getDay();
        const e_x_min = e_col*colWidth()+1;
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
function getModifiedTimes(e) {
    const dx = (e === selected_event) ? selected_event_x_final - selected_event_x_init : 0;
    const dy = (e === selected_event) ? selected_event_y_final - selected_event_y_init : 0;
    const dt = dyTodt(dy);

    const dest_start_date = new Date(e.start_date.getTime());
    if (!selected_bot) {
        dest_start_date.setHours(dest_start_date.getHours() + dt.getHours());
        dest_start_date.setMinutes(dest_start_date.getMinutes() + dt.getMinutes());
    }

    const dest_end_date = new Date(e.end_date.getTime());
    if (!selected_top) {
        dest_end_date.setHours(dest_end_date.getHours() + dt.getHours());
        dest_end_date.setMinutes(dest_end_date.getMinutes() + dt.getMinutes());
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

function mousedown(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? true : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? true : mouse_right_btn_down;

    mouse_down_x = clientToCanvasX(e.clientX);
    mouse_down_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        //selected_event = getClickedEvent(mouse_down_x, mouse_down_y);
        setClickedEvent(mouse_down_x, mouse_down_y);
        if (selected_event === null) {
            new_event_active = true;
            new_event_x_init = mouse_down_x;
            new_event_y_init = mouse_down_y;
            new_event_x_final = mouse_down_x;
            new_event_y_final = mouse_down_y;
        } else {
            selected_event_x_init = mouse_down_x;
            selected_event_y_init = mouse_down_y;
            selected_event_x_final = mouse_down_x;
            selected_event_y_final = mouse_down_y;
        }
    }
}

function mouseup(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? false : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? false : mouse_right_btn_down;

    if (e.button == LEFT_MOUSE_BUTTON) {
        if (new_event_active) {
            const dy = new_event_y_final - new_event_y_init;
            if (dy > MIN_DY) {
                //const start_date = xyToDateTime(new_event_x_init, new_event_y_init);
                //const end_date = xyToDateTime(new_event_x_final, new_event_y_final);
                const title = prompt("Enter a title for the event", "New Event");
                if (title !== null) {
                    _createEvent(title, "My Event", "Somewhere",
                        new_event.start_date.getSQLDate(),
                        new_event.start_date.getSQLTime(),
                        new_event.end_date.getSQLDate(),
                        new_event.end_date.getSQLTime());
                }
            }
            new_event_active = false;
        }
        if (selected_event !== null) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(selected_event);
            if (dest_start_date.getTime() !== selected_event.start_date.getTime() ||
                dest_end_date.getTime() !== selected_event.end_date.getTime()) {
                modifyEvent(selected_event.id, dest_start_date.getSQLTime(), dest_end_date.getSQLTime());
            }
        }
    } else if (e.button == RIGHT_MOUSE_BUTTON) {
    }
}

function mousemove(e) {

    mouse_x = clientToCanvasX(e.clientX);
    mouse_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        if (selected_event === null) {
            new_event_x_final = mouse_x;
            new_event_y_final = mouse_y;
        } else {
            selected_event_x_final = mouse_x;
            selected_event_y_final = mouse_y;
        }
    }
}

function keydown(e) {
    if (e.key == "?") {
        fetch('getErrors.php')
          .then(response => response.text())
          .then(data => {console.log(data); });
    } else if (e.key == "[") {
        if (grid_idx > 0) { grid_idx--; }
        grid_size = grid_presets[grid_idx];
    } else if (e.key == "]") {
        if (grid_idx < grid_presets.length-1) { grid_idx++; }
        grid_size = grid_presets[grid_idx];
    } else if (e.key == "m") {
        console.log(mousePosToDateTime());
    } else if (e.key == "s") {
        snap_to_grid = !snap_to_grid;
        console.log("snap to grid: ", snap_to_grid);
    } else if (e.key == "r") {
        getEvents();
        //window.location.reload();
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

function resize() {
    canvas.width = window.innerWidth - 2;
    canvas.height = window.innerHeight - canvas.offsetTop;
    //canvas.height -= ADDRESS_BAR_HEIGHT; // TODO
    can_w = canvas.width;
    can_h = canvas.height;
}

// TODO: should this call getEvents()?
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
    $(window).resize(resize);

    setOriginDateFromToday();

    draw();

    getEvents();

});

function checkForUpdates() {
    $.get('mt_functions.php', {func: 'checkForUpdates'})
    .done(function(data) {
        console.log(data);
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
        events = []; // TODO: instead of clearing, match fetched events with existing events by id
        selected_event = null;
        for (const e of data) {
            const start_date = getDateFromSQL(e.start_date, e.start_time);
            const end_date = getDateFromSQL(e.end_date, e.end_time);
            events.push({title: e.title, start_date: start_date, end_date: end_date, color: 'blue', id: e.id });
        }
    });
}

function deleteEvent(eventID) {
    $.post('mt_functions.php', {
        func: 'deleteEvent',
        event_id: eventID
    })
    .done(function(data) {
        console.log(data);
        getEvents();
    });
}

function modifyEvent(eventID, startTime, endTime) {
    $.post('mt_functions.php', {
        func: 'modifyEvent',
        event_id: eventID,
        start_time: startTime,
        end_time: endTime,
    })
    .done(function(data) {
        console.log(data);
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
