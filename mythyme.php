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

        <div>
            <button onclick="checkForUpdates()">checkForUpdates</button><br />
            <button onclick="test()">test</button><br />
        </div>

        <!-- TODO: consider using async form post -->
        <!-- see: https://pqina.nl/blog/async-form-posts-with-a-couple-lines-of-vanilla-javascript/ -->
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

        <script>
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
      //.then(response => response.json())
      .then(response => response.text())
      .then(data => {console.log(data); console.timeEnd(timer_str); });

    return 'end of test';
}

$(function() {
    document.addEventListener('keydown', function(event){
        //console.log(event);
        if (event.key == "?") {
            fetch('getErrors.php')
              .then(response => response.text())
              .then(data => {console.log(data); });
        } else if (event.key == "t") {
            //test();
        }
    });
});

function checkForUpdates() {
    $.get('mt_functions.php', {func: 'checkForUpdates'})
    .done(function(data) {
        console.log(data);
    });
}

function createEventButtonFunc() {
    const eventTitle        = $('#eventTitle').val();
    const eventDescription  = $('#eventDescription').val();
    const eventLocation     = $('#eventLocation').val();
    const startDate         = $('#eventStartDate').val();
    const startTime         = $('#eventStartTime').val();
    const endDate           = $('#eventEndDate').val();
    const endTime           = $('#eventEndTime').val();
    _createEvent(eventTitle, eventDescription, eventLocation, startDate, startTime, endDate, endTime);
}

function deleteEventButtonFunc() {
    const eventID = $('#eventID').val();
    deleteEvent(eventID);
}

function _createEvent(eventTitle, eventDescription, eventLocation, startDate, startTime, endDate, endTime) {
    console.log(startDate);
    console.log(startTime);
    console.log(endDate);
    console.log(endTime);
    //$.get('mt_functions.php', {
    //    func: 'createEvent',
    //    title: eventTitle,
    //    desc: eventDescription,
    //    location: eventLocation,
    //    start_date: startDate,
    //    start_time: startTime,
    //    end_date: endDate,
    //    end_time: endTime
    //})
    //.done(function(data) {
    //    console.log(data);
    //});
}

function getEvents() {
    $.get('mt_functions.php', {
        func: 'getEvents'
    })
    .done(function(data) {
        console.log(data);
    });
}

function deleteEvent(eventID) {
    $.get('mt_functions.php', {
        func: 'deleteEvent',
        event_id: eventID
    })
    .done(function(data) {
        console.log(data);
    });
}

function modifyEvent() {
    $.get('mt_functions.php', {
        func: 'modifyEvent'
    })
    .done(function(data) {
        console.log(data);
    });
}
        </script>
    </body>

</html>
