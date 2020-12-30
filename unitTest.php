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
        <meta name="description" content="MyThyme Unit Test" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>MyThyme Unit Test</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    </head>

    <body>

<script>

$(function() {
    document.addEventListener('keydown', function(event){
        if (event.key == "?") {
            fetch('getErrors.php')
              .then(response => response.text())
              .then(data => {console.log(data); });
        }
    });
});

    async function postData(url = '', data = {}) {
        const query = new URLSearchParams();
        Object.entries(data).forEach(entry => query.append(entry[0], entry[1]));
        const postBody = query.toString();
        const response = await fetch(url, {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            //headers: { 'Content-Type': 'application/json' },
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            redirect: 'follow',
            //body: JSON.stringify(data)
            body: postBody
        });
        return response.json();
    }

    async function createEventsTable() {
        await postData("mt_functions.php", {func: 'createEventsTable', test: '1'})
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

    async function checkForUpdates() {
        const query = new URLSearchParams();
        query.append("func", "checkForUpdates");
        query.append("test", "1");
        const url = "mt_functions.php?" + query.toString();
        await fetch(url)
            .then(response => response.json())
            .then(data => { console.log(data); } )
            .catch(error => { console.error(error); } );
    }

    async function _createEvent(title, desc, _location, start_date, start_time, end_date, end_time) {
        // TODO: get the id of the newly created event so it can be deleted
        await postData("mt_functions.php", {
            func: "createEvent",
            test: "1",
            title: title,
            desc: desc,
            location: _location,
            start_date: start_date,
            start_time: start_time,
            end_date: end_date,
            end_time: end_time
        })
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error);} );
    }

    async function getEvents(begin_date, end_date) {
        const query = new URLSearchParams();
        query.append('func', 'getEvents');
        query.append("test", "1");
        query.append("begin_date", begin_date);
        query.append("end_date", end_date);
        const url = 'mt_functions.php?' + query.toString();
        await fetch(url)
            .then(response => response.json())
            .then(data => { console.log(data);} )
            .catch(error => { console.error(error);} );
    }

    async function deleteEvent() {
        await postData("mt_functions.php", {
            func: "deleteEvent",
            test: "1",
            event_id: "0"
        })
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

    async function modifyEvent() {
        // TODO: add parameters
        await postData("mt_functions.php", {
            func: "modifyEvent",
            test: "1",
            event_id: "0"
        })
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

    async function destroyEventsTable() {
        await postData("mt_functions.php", {func: 'destroyEventsTable', test: '1'})
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

/* test procedure */

    // TODO: enforce ordering of the fetch requests

    (async function () {

        await createEventsTable();

        await checkForUpdates();

        await _createEvent("new_event", "New Event", "Maine", "January 01, 2021", "09:30", "2021-01-01", "10:30");
        await _createEvent("new_event", "New Event", "Maine", "January 01, 2021", "09:30", "2020-12-28", "10:30"); // should cause error
        await _createEvent("new_event", "New Event", "Maine", "January 01, 2020", "09:30", "2020-01-01", "10:30");

        await getEvents("2021-01-01", "2021-01-07");

        await deleteEvent();

        await modifyEvent();

        await destroyEventsTable();

    })();

</script>

    </body>

</html>
