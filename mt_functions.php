<?php
require '../../php/dbh.inc.php';

session_start();

if (!isset($_SESSION['username'])) {
    echo 'ERROR: username not defined';
    exit();
}

if (isset($_GET['func'])) {
    $func = $_GET['func'];

    switch($func) {
    case 'checkForUpdates':
        checkForUpdates();
        break;
    case 'createEvent':
        if (
            !isset($_GET['title']) or
            !isset($_GET['start_date']) or
            !isset($_GET['start_time']) or
            !isset($_GET['end_date']) or
            !isset($_GET['end_time'])
        ) {
            exit('ERROR: must specify title, start_date, start_time, end_date, and end_time');
        } else {
            $title = $_GET['title'];
            $desc = $_GET['desc']; // check if this is set
            $location = $_GET['location']; // check if this is set
            $start_date = $_GET['start_date'];
            $start_time = $_GET['start_time'];
            $end_date = $_GET['end_date'];
            $end_time = $_GET['end_time'];
            createEvent($title, $desc, $location, $start_date, $start_time, $end_date, $end_time);
        }
        break;
    case 'getEvents':
        getEvents();
        break;
    case 'deleteEvent':
        if (!isset($_GET['event_id'])) {
            exit('ERROR: must specify event_id');
        } else {
            $event_id = $_GET['event_id'];
            deleteEvent($event_id);
        }
        break;
    case 'modifyEvent':
        modifyEvent();
        break;
    default:
        echo 'ERROR: invalid function designation ' . $_GET['func'];
        break;
    }
} else {
    echo 'ERROR: func parameter not set';
    exit();
}

function checkForUpdates()
{
    echo 'checking for updates...';
}

function createEvent($title, $desc, $location, $start_date, $start_time, $end_date, $end_time)
{
    global $conn;
    // TODO:
    // * check that end date/time comes after start date/time
    // * check that date and time variables are valid date and time strings
    if (($title == '') or ($start_date == '') or ($start_time == '') or ($end_date == '') or ($end_time == '')) {
        exit("ERROR: must specify title, start_date, start_time, end_date, and end_time");
    }
    echo "creating event: ($title, $desc, $location, $start_date, $start_time, $end_date, $end_time)...";

    $query = "INSERT INTO events (title, description, start_date, start_time, end_date, end_time, location, owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $owner = $_SESSION['username'];

    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $query)) {
        exit('mysqli_stmt_prepare failed');
    } else {
        mysqli_stmt_bind_param($stmt, "ssssssss", $title, $desc, $start_date, $start_time, $end_date, $end_time, $location, $owner);
        mysqli_stmt_execute($stmt);
        echo "done.\n";
    }
}

// TODO: return a json object of the events between some range of dates (or satisfying some criteria)
function getEvents()
{
    global $conn;
    $get_events_query = "SELECT * FROM events";
    $get_events_stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($get_events_stmt, $get_events_query)) {
        returnError('mysqli_stmt_prepare failed');
    } else {
        mysqli_stmt_execute($get_events_stmt);
        $result = mysqli_stmt_get_result($get_events_stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $event_state_json = $row['title'];
        } else {
            $event_state_json = "";
        }
    }
    echo $event_state_json;
}

function deleteEvent($event_id)
{
    // TODO: check to make sure username == owner (or that permissions are satisfied)
    // TODO: check to make sure event with id event_id exists
    global $conn;
    echo "deleting event with id $event_id...";
    $query = "DELETE FROM events WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $query)) {
        returnError('mysqli_stmt_prepare failed');
    } else {
        mysqli_stmt_bind_param($stmt, "i", intval($event_id));
        mysqli_stmt_execute($stmt);
    }
    echo 'deleted';
}

function modifyEvent()
{
    echo 'modifyEvent';
}

?>
