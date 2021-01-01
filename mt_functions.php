<?php
require '../../php/dbh.inc.php';

session_start();

if (!isset($_SESSION['username'])) {
    returnJsonHttpResponse(400, 'ERROR: username not defined');
}

// TODO: consider giving each error a unique number
//       and having a message associated with each error type
//       and returning the error number and msg as JSON

/* ==== dispatch HTTP requests ==== */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['func'])) {
        returnJsonHttpResponse(400, "ERROR: 'func' parameter not set");
    }
    $func = $_POST['func'];
    $table = ($_POST['test']) ? 'test_events' : 'events';

    switch($func) {
    case 'createEventsTable':
        createEventsTable();
        break;
    case 'destroyEventsTable':
        destroyEventsTable();
        break;
    case 'createEvent':
        if (
            !isset($_POST['title']) or
            !isset($_POST['start_date']) or
            !isset($_POST['start_time']) or
            !isset($_POST['end_date']) or
            !isset($_POST['end_time'])
        ) {
            returnJsonHttpResponse(400, 'ERROR: must specify title, start_date, start_time, end_date, and end_time');
        }
        $title = $_POST['title'];
        $desc = $_POST['desc']; // check if this is set
        $location = $_POST['location']; // check if this is set
        $start_date = $_POST['start_date'];
        $start_time = $_POST['start_time'];
        $end_date = $_POST['end_date'];
        $end_time = $_POST['end_time'];
        createEvent($title, $desc, $location, $start_date, $start_time, $end_date, $end_time);
        break;
    case 'deleteEvent':
        if (!isset($_POST['event_id'])) {
            returnJsonHttpResponse(400, 'ERROR: must specify event_id');
        }
        $event_id = $_POST['event_id'];
        deleteEvent($event_id);
        break;
    case 'modifyEvent':
        if (
            !isset($_POST['event_id']) or 
            !isset($_POST['start_time']) or 
            !isset($_POST['end_time'])
        ) {
            returnJsonHttpResponse(400, 'ERROR: must specify event_id, new start_time and new end_time');
        }
        $event_id = $_POST['event_id'];
        $new_start_time = $_POST['start_time'];
        $new_end_time = $_POST['end_time'];
        modifyEvent($event_id, $new_start_time, $new_end_time);
        break;
    default:
        returnJsonHttpResponse(400, 'ERROR: invalid function designation ' . $_POST['func']);
        break;
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!isset($_GET['func'])) {
        returnJsonHttpResponse(400, "ERROR: 'func' parameter not set");
    }
    $func = $_GET['func'];
    $table = ($_GET['test']) ? 'test_events' : 'events';

    switch($func) {
    case 'checkForUpdates':
        // TODO: use GET params
        checkForUpdates(0, 0);
        break;
    case 'getEvents':
        if (!isset($_GET['begin_date']) or !isset($_GET['end_date'])) {
            returnJsonHttpResponse(400, 'ERROR: must specify begin_date and end_date');
        }
        $begin_date = $_GET['begin_date'];
        $end_date = $_GET['end_date'];
        getEvents($begin_date, $end_date);
        break;
    default:
        returnJsonHttpResponse(400, 'ERROR: invalid function designation ' . $_GET['func']);
        break;
    }
} else {
    returnJsonHttpResponse(400, "ERROR: '" . $_SERVER["REQUEST_METHOD"] . " not a valid method");
}

/* ==== API ==== */

function createEventsTable()
{
    global $conn;
    global $table;
    $query = "CREATE TABLE `$table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` tinytext NOT NULL,
  `description` longtext,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` longtext,
  `owner` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1";

    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt, $query);
    mysqli_stmt_execute_e($stmt);
    returnJsonHttpResponse(200, "test_events table created");
}

function destroyEventsTable()
{
    global $conn;
    global $table;
    $query = "DROP TABLE $table";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt, $query);
    mysqli_stmt_execute_e($stmt);
    returnJsonHttpResponse(200, "test_events table destroyed");
}

// TODO
function checkForUpdates($start_date, $end_date)
{
    returnJsonHttpResponse(200, "checkForUpdates TBD");
}

function createEvent($title, $desc, $location, $start_date, $start_time, $end_date, $end_time)
{
    global $conn;
    global $table;

    if (empty($title) or empty($start_date) or empty($start_time) or empty($end_date) or empty($end_time)) {
        returnJsonHttpResponse(400, "ERROR: must specify title, start_date, start_time, end_date, and end_time");
    }

    if(!strtotime($start_date)) { returnJsonHttpResponse(400, "ERROR: invalid start_date '$start_date'"); }
    if(!strtotime($start_time)) { returnJsonHttpResponse(400, "ERROR: invalid start_time '$start_time'"); }
    if(!strtotime($end_date)) { returnJsonHttpResponse(400, "ERROR: invalid end_date '$end_date'"); }
    if(!strtotime($end_time)) { returnJsonHttpResponse(400, "ERROR: invalid end_time '$end_time'"); }

    $sdate_sql = date_format(date_create($start_date), "Y-m-d");
    $stime_sql = date_format(date_create($start_time), "H:i:s");
    $edate_sql = date_format(date_create($end_date), "Y-m-d");
    $etime_sql = date_format(date_create($end_time), "H:i:s");

    if ($edate_sql < $sdate_sql) { returnJsonHttpResponse(400, "ERROR: invalid start/end dates"); }
    if (($sdate_sql == $edate_sql) and ($etime_sql <= $stime_sql)) { returnJsonHttpResponse(400, "ERROR: invalid start/end times"); }

    $query = "INSERT INTO $table (title, description, start_date, start_time, end_date, end_time, location, owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // TODO: owner field is currently an int; need to retrieve id of user
    $owner = $_SESSION['username'];
    $owner_id = 0;

    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt, $query);
    mysqli_stmt_bind_param_e($stmt, "sssssssi", $title, $desc, $sdate_sql, $stime_sql, $edate_sql, $etime_sql, $location, $owner_id);
    mysqli_stmt_execute_e($stmt);

    $event_id = mysqli_insert_id($conn);

    returnJsonHttpResponse(200, "added event with event_id $event_id ($owner)");
}

function getEvents($begin_date, $end_date)
{
    global $conn;
    global $table;
    $query = "SELECT * FROM $table WHERE end_date >= ? AND start_date <= ?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt , $query);
    mysqli_stmt_bind_param_e($stmt, "ss", $begin_date, $end_date);
    mysqli_stmt_execute_e($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($data, $row);
    }

    returnJsonHttpResponse(200, $data);
}

function deleteEvent($event_id)
{
    // TODO: check to make sure username == owner (or that permissions are satisfied)
    // TODO: check to make sure event with id event_id exists
    global $conn;
    global $table;
    $query = "DELETE FROM $table WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt, $query);
    mysqli_stmt_bind_param_e($stmt, "i", intval($event_id));
    mysqli_stmt_execute_e($stmt);
    returnJsonHttpResponse(200, "event deleted");
}

// TODO: allow any combination of fields to be updated
function modifyEvent($event_id, $new_start_time, $new_end_time)
{
    global $conn;
    global $table;

    /* validate parameters */

    if (empty($event_id) or empty($new_start_time) or empty($new_end_time)) {
        returnJsonHttpResponse(400, "ERROR: must specify event_id, new start_time, and new end_time");
    }

    if(!strtotime($new_start_time)) { returnJsonHttpResponse(400, "ERROR: invalid start_time '$new_start_time'"); }
    if(!strtotime($new_end_time)) { returnJsonHttpResponse(400, "ERROR: invalid end_time '$new_end_time'"); }

    $start_time_sql = date_format(date_create($new_start_time), "H:i:s");
    $end_time_sql = date_format(date_create($new_end_time), "H:i:s");

    if ($end_time_sql <= $start_time_sql) { returnJsonHttpResponse(400, "ERROR: invalid start/end times"); }

    /* update event */

    $query = "UPDATE $table SET start_time = ?, end_time = ? WHERE id = ?";
    $stmt = mysqli_stmt_init($conn);
    mysqli_stmt_prepare_e($stmt, $query);
    mysqli_stmt_bind_param_e($stmt, "ssi", $start_time_sql, $end_time_sql, intval($event_id));
    mysqli_stmt_execute_e($stmt);
    returnJsonHttpResponse(200, "event $event_id modified");
}

/* ==== helper functions ==== */

function returnJsonHttpResponse($code, $data)
{
    ob_clean();
    header_remove(); 
    header("Content-type: application/json; charset=utf-8");
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function mysqli_stmt_prepare_e($stmt, $query) {
    if (!mysqli_stmt_prepare($stmt, $query)) {
        returnJsonHttpResponse(400, "mysqli_stmt_prepare failed");
    }
}

function mysqli_stmt_bind_param_e($stmt, $types, ...$args) {
    if (!mysqli_stmt_bind_param($stmt, $types, ...array_slice(func_get_args(),2))) {
        returnJsonHttpResponse(500, "ERROR: mysqli_stmt_bind_param");
    }
}

function mysqli_stmt_execute_e($stmt) {
    if (!mysqli_stmt_execute($stmt)) {
        returnJsonHttpResponse(500, "ERROR: mysqli_stmt_execute");
    }
}

// TODO: consider making error-handlers for mysqli_stmt_get_result, mysqli_fetch_assoc

?>
