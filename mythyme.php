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
        </div>

        <div>
            <input type="text" name="eventTitle" placeholder="Title..." /><br />
            <input type="text" name="eventDescription" placeholder="Description..." /><br />
            <input type="text" name="eventLocation" placeholder="Location..." /><br />
            <label for="eventStartDate">Start Date</label>
            <input type="date" id="eventStartDate"></input>
            <label for="eventStartTime">Start Time</label>
            <input type="time" id="eventStartTime"></input><br />
            <label for="eventEndDate">End Date</label>
            <input type="date" id="eventEndDate"></input>
            <label for="eventEndTime">End Time</label>
            <input type="time" id="eventEndTime"></input><br />
            <button onclick="_createEvent()">createEvent</button>
        </div>

        <button onclick="getEvents()">getEvents</button><br />

        <button onclick="deleteEvent()">deleteEvent</button><br />

        <button onclick="modifyEvent()">modifyEvent</button><br />

        <script>
$(function() {
    document.addEventListener('keydown', function(event){
        //console.log(event);
        if (event.key == "?") {
            foo = {s: "hi", t: 4};
            bar = {s: "there", t: 5};
            baz = {s: "you", t: 3};
            snap = {r: "you", w: 3};
            console.log('%c Hello, world', 'color: orange; font-weight: bold;');
            console.table([foo, bar, baz, snap], ['s','t']);
            console.time('x');
            let i = 0;
            while(i < 1000000) { i++; }
            console.timeEnd('x');
            console.dir(foo);
            console.dir(checkForUpdates);
            console.trace('my trace');
        }
    });
});

function checkForUpdates() {
    $.get('mt_functions.php', {func: 'checkForUpdates'})
    .done(function(data) {
        console.log(data);
    });
}

function _createEvent() {
    const startDate = $('#eventStartDate').val();
    const startTime = $('#eventStartTime').val();
    const endDate = $('#eventEndDate').val();
    const endTime = $('#eventEndTime').val();
    $.get('mt_functions.php', {
        func: 'createEvent',
        start_date: startDate,
        start_time: startTime,
        end_date: endDate,
        end_time: endTime
    })
    .done(function(data) {
        console.log(data);
    });
}

function getEvents() {
    $.get('mt_functions.php', {
        func: 'getEvents'
    })
    .done(function(data) {
        console.log(data);
    });
}

function deleteEvent() {
    $.get('mt_functions.php', {
        func: 'deleteEvent'
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
