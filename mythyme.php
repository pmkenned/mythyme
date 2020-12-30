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

<!--
        <div>
            <button onclick="checkForUpdates()">checkForUpdates</button><br />
            <button onclick="test()">test</button><br />
        </div>

        <div>
            <input type="text" id="eventTitle" placeholder="Title..." /><br />
            <input type="text" id="eventDescription" placeholder="Description..." /><br />
            <input type="text" id="eventLocation" placeholder="Location..." /><br />
            <label for="eventStartDate">Start Date</label>
            <input type="date" id="eventStartDate"></input>
            <label for="eventStartTime">Start Time</label>
            <input type="time" id="eventStartTime"></input><br />
            <label for="eventEndDate">End Date</label>
            <input type="date" id="eventEndDate"></input>
            <label for="eventEndTime">End Time</label>
            <input type="time" id="eventEndTime"></input><br />
            <button onclick="createEventButtonFunc()">createEvent</button>
        </div>

        <button onclick="getEvents()">getEvents</button><br />

        <label for="eventID">Event ID</label>
        <input type="number" id="eventID" value="1"></input>
        <button onclick="deleteEventButtonFunc()">deleteEvent</button><br />

        <button onclick="modifyEvent()">modifyEvent</button><br />
-->

        <script>

const CLIENT_Y_OFFSET = 30;
const HOURS_IN_DAY = 24;
const DAYS_IN_WEEK = 7;

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
let w;
let h;

let origin_date;
let next_origin_date;

let mouse_left_btn_down = false;
let mouse_right_btn_down = false;

let selected_event = null;
let selected_event_y_px_off;
let selected_event_y_px_off_init;

let new_event_active = false;
let new_event_col;
let new_event_top_px;
let new_event_bot_px;

// NOTE: JavaScript's Date() constructor starts months at 0!
let events = [
    //{title: 'Event 1', start_date: '2020-12-28', end_date: '2020-12-28', start_time: '09:30', end_time: '10:30' },
    //{title: 'Event 2', start_date: '2020-12-29', end_date: '2020-12-29', start_time: '10:15', end_time: '12:30' },

    //{title: 'Event 1', start_date: new Date(2020,11,28), end_date: new Date(2020,11,28), start_time: '09:30', end_time: '10:30' },
    //{title: 'Event 2', start_date: new Date(2020,11,29), end_date: new Date(2020,11,29), start_time: '10:15', end_time: '12:30' },
    //{title: 'Event 3', start_date: new Date(2020,11,26), end_date: new Date(2020,11,26), start_time: '10:15', end_time: '12:30' },

    {title: 'Event 1', start_date: new Date(2020,11,28,9,30), end_date: new Date(2020,11,28,10,30), color: 'red' },
    {title: 'Event 2', start_date: new Date(2020,11,29,10,15), end_date: new Date(2020,11,29,12,30), color: 'blue' },
    {title: 'Event 3', start_date: new Date(2020,11,26,10,15), end_date: new Date(2020,11,26,12,30), color: 'green' },
    {title: 'Event 4', start_date: new Date(2020,11,27,10,15), end_date: new Date(2020,11,27,12,30), color: 'orange' },
    {title: 'Event 5', start_date: new Date(2021,00,01,13,45), end_date: new Date(2021,00,01,14,00), color: 'purple' }
];
events = [];

function getDateFromSQL(sqlDate, sqlTime) {
    const year = parseInt(sqlDate.split('-')[0]);
    const month = parseInt(sqlDate.split('-')[1]) - 1;
    const _date = parseInt(sqlDate.split('-')[2]);
    const hour = parseInt(sqlTime.split(':')[0]);
    const min = parseInt(sqlTime.split(':')[1]);
    return new Date(year, month, _date, hour, min);
}

// TODO: tidy this
function clientToCanvasY(y) { return y - CLIENT_Y_OFFSET; }
function clientToCanvasX(x) { return x; }

function to_px(x) {
    return Math.round(x) + 0.5;
}

function draw(timestamp) {
    ctx.clearRect(0, 0, w, h);

    ctx.strokeStyle = "black";
    ctx.fillStyle = "black";
    ctx.font = "15px Arial";

    for (let i = 0; i < DAYS_IN_WEEK; i++) {
        if (true || (i > 0)) {
            ctx.beginPath();
            ctx.moveTo(to_px(i*w/DAYS_IN_WEEK), 0);
            ctx.lineTo(to_px(i*w/DAYS_IN_WEEK), h);
            ctx.closePath();
            ctx.stroke();
        }
        let col_date = new Date(origin_date.getTime());
        col_date.setDate(col_date.getDate() + i);
        const month = monthNamesShort[col_date.getMonth()];
        const _date = col_date.getDate();
        ctx.fillText(dayNames[i], to_px(i*w/DAYS_IN_WEEK), 20);
        ctx.fillText(`${month}, ${_date}`, to_px(i*w/DAYS_IN_WEEK), 40);
    }

    for (let i = 0; i < HOURS_IN_DAY+1; i++) {
        if (i > 0) {
            ctx.beginPath();
            ctx.moveTo(0, to_px(i*h/(HOURS_IN_DAY+1)));
            ctx.lineTo(w, to_px(i*h/(HOURS_IN_DAY+1)));
            ctx.closePath();
            ctx.stroke();
        }
    }

    ctx.font = "10px Arial";
    for (const e of events) {
        if ((e.end_date >= origin_date) && (e.start_date <= next_origin_date)) {

            if (e === selected_event) {
                const day = e.start_date.getDay();

                const shour = e.start_date.getHours();
                const smin = e.start_date.getMinutes();

                const ehour = e.end_date.getHours();
                const emin = e.end_date.getMinutes();

                const dy = (selected_event_y_px_off - selected_event_y_px_off_init);
                const top_px = Math.round((shour+smin/60.0)*h/(HOURS_IN_DAY+1)) + dy;
                const bot_px = Math.round((ehour+emin/60.0)*h/(HOURS_IN_DAY+1)) + dy;

                ctx.fillStyle = "cyan";
                ctx.fillText(e.title, day*w/7, top_px);
                ctx.fillRect(Math.round(day*w/7)+1, top_px, Math.round(w/7)-1, bot_px - top_px);
            } else {
                const day = e.start_date.getDay();

                const shour = e.start_date.getHours();
                const smin = e.start_date.getMinutes();

                const ehour = e.end_date.getHours();
                const emin = e.end_date.getMinutes();

                const top_px = Math.round((shour+smin/60.0)*h/(HOURS_IN_DAY+1));
                const bot_px = Math.round((ehour+emin/60.0)*h/(HOURS_IN_DAY+1));

                ctx.fillStyle = e.color;
                ctx.fillText(e.title, day*w/7, top_px);
                ctx.fillRect(Math.round(day*w/7)+1, top_px, Math.round(w/7)-1, bot_px - top_px);
            }
        }
    }

    ctx.fillStyle = "red";
    if (new_event_active) {
        ctx.fillRect(Math.round(new_event_col*w/7)+1, new_event_top_px, Math.round(w/7)-1, new_event_bot_px - new_event_top_px);
    }

    window.requestAnimationFrame(draw);
}

// TODO: encapsulate the calculations used here
function checkForEventClick(canvas_x, canvas_y) {
    for (const e of events) {
        const day = e.start_date.getDay();
        const event_x_min = Math.round(day*w/7)+1;
        const event_x_max = event_x_min + Math.round(w/7);
        const shour = e.start_date.getHours();
        const smin = e.start_date.getMinutes();
        const ehour = e.end_date.getHours();
        const emin = e.end_date.getMinutes();
        const top_px = Math.round((shour+smin/60.0)*h/(HOURS_IN_DAY+1));
        const bot_px = Math.round((ehour+emin/60.0)*h/(HOURS_IN_DAY+1));
        if ((canvas_x >= event_x_min && canvas_x <= event_x_max) && (canvas_y >= top_px && canvas_y <= bot_px)) {
            return e;
        }
    }
    return null;
}

function colPxToDateTime(col, px) {
    let d = new Date(origin_date.getTime());
    d.setDate(d.getDate() + col);
    const hourHeight = Math.round(h/(HOURS_IN_DAY+1));
    d.setHours(Math.floor((px-hourHeight)/hourHeight));
    d.setMinutes(Math.round(60*(px % hourHeight)/hourHeight));
    d.setSeconds(0);
    return d;
}

function mousedown(e) {
    mouse_left_btn_down = (e.button == 0) ? true : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == 2) ? true : mouse_right_btn_down;

    if (mouse_left_btn_down) {
        const canvas_x = clientToCanvasX(e.clientX);
        const canvas_y = clientToCanvasY(e.clientY);
        selected_event = checkForEventClick(canvas_x, canvas_y);
        if (selected_event === null) {
			//new_event_active = true;
			new_event_col    = Math.floor(e.clientX*DAYS_IN_WEEK/w);
			new_event_top_px = canvas_y;
			new_event_bot_px = canvas_y;
        } else {
            selected_event_y_px_off_init = canvas_y;
            selected_event_y_px_off = canvas_y;
        }
    }
}

function mouseup(e) {
    mouse_left_btn_down = (e.button == 0) ? false : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == 2) ? false : mouse_right_btn_down;

    if (!mouse_left_btn_down) {
        if (new_event_active) {
            let start_date = colPxToDateTime(new_event_col, new_event_top_px);
            let end_date = colPxToDateTime(new_event_col, new_event_bot_px);
            let title = prompt("Enter a title for the event", "New Event");
            if (title !== null) {
                _createEvent(title, "My Event", "Somewhere",
                    start_date.getSQLDate(),
                    start_date.getSQLTime(),
                    end_date.getSQLDate(),
                    end_date.getSQLTime());
            }
            new_event_active = false;
        }
        if (selected_event !== null) {
            // TODO: modify event according to y-offset
            //const new_date = colPxToDateTime(4, selected_event_y_px_off);
            selected_event_y_px_off = selected_event_y_px_off_init;
            const start_date = new Date(selected_event.start_date.getTime());
            start_date.setHours(start_date.getHours() + 1);
            const end_date = new Date(selected_event.end_date.getTime());
            end_date.setHours(end_date.getHours() + 1);
            const new_start_time = start_date.getSQLTime();
            const new_end_time = end_date.getSQLTime();
            modifyEvent(selected_event.id, new_start_time, new_end_time);
        }
    }
}

// TODO: if user clicks without moving mouse, make event of default duration?
//       alternatively, only create an event if the mouse has been moved a certain minimum amount
function mousemove(e) {
    const canvas_y = clientToCanvasY(e.clientY);
    if (mouse_left_btn_down) {
        if (selected_event === null) {
			new_event_active = true;
            new_event_bot_px = canvas_y;
        } else {
            selected_event_y_px_off = canvas_y;
        }
    }
}

function keydown(e) {
    if (e.key == "?") {
        fetch('getErrors.php')
          .then(response => response.text())
          .then(data => {console.log(data); });
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
    canvas.height = window.innerHeight - 30; // TODO: calculate height of "Logged in as" div
    w = canvas.width;
    h = canvas.height;
}

// TODO: should this call getEvents()?
function setOriginDateFromToday() {
    origin_date = new Date()
    origin_date.setDate((new Date()).getDate() - (new Date()).getDay());
    origin_date.setHours(0);
    origin_date.setMinutes(0);
    next_origin_date = new Date(origin_date.getTime());
    next_origin_date.setDate(origin_date.getDate() + 7);
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
        let d = this;
        const month = `${d.getMonth()+1}`.lpad("0", 2);
        const _date = `${d.getDate()}`.lpad("0", 2);
        return `${d.getFullYear()}-${month}-${_date}`;
    }

    Date.prototype.getSQLTime = function() {
        let d = this;
        const hour = `${d.getHours()+1}`.lpad("0", 2);
        const min = `${d.getMinutes()}`.lpad("0", 2);
        return `${hour}:${min}:00`;
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

//function createEventButtonFunc() {
//    const eventTitle        = $('#eventTitle').val();
//    const eventDescription  = $('#eventDescription').val();
//    const eventLocation     = $('#eventLocation').val();
//    const startDate         = $('#eventStartDate').val();
//    const startTime         = $('#eventStartTime').val();
//    const endDate           = $('#eventEndDate').val();
//    const endTime           = $('#eventEndTime').val();
//    _createEvent(eventTitle, eventDescription, eventLocation, startDate, startTime, endDate, endTime);
//}

//function deleteEventButtonFunc() {
//    const eventID = $('#eventID').val();
//    deleteEvent(eventID);
//}

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
        events = [];
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

let timer_num = 0;

function test() {
    foo = {s: "hi", t: 4};
    bar = {s: "there", t: 5};
    baz = {s: "you", t: 3};
    console.log('%c Hello, world', 'color: orange; font-weight: bold;');
    console.table([foo, bar, baz], ['s','t']);
    console.dir(foo);
    console.dir(checkForUpdates);
    console.trace('my trace');

    console.groupCollapsed();
        console.warn('this is a warning');
        console.info('this is info');
        console.error('this is an error');
        console.assert(1==2, '1 doesn\'t equal two');
    console.groupEnd()
    //console.clear();
    console.count();

    const timer_str = `${timer_num}`;
    timer_num++;
    console.time(timer_str);
    fetch('mt_functions.php?func=checkForUpdates')
      .then(response => response.json())
      .then(data => {console.log(data); console.timeEnd(timer_str); });

    return 'end of test';
}

        </script>
    </body>

</html>
