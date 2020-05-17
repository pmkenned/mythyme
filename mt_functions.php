<?php

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
        createEvent();
        break;
    case 'getEvents':
        getEvents();
        break;
    case 'deleteEvent':
        deleteEvent();
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

function createEvent()
{
    echo 'creating event: ' . $_GET['start_date'] . ' ' . $_GET['start_time'] . ' ' . $_GET['end_date'] . ' ' . $_GET['end_time'];
    // TODO: add an event to 'events' table
}

function getEvents()
{
    echo 'getEvents';
}

function deleteEvent()
{
    echo 'deleteEvent';
}

function modifyEvent()
{
    echo 'modifyEvent';
}

?>
