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

    /* NOTE: ideally, the data would go in the body of the post
     * however, for some reason (perhaps having to do with https rewrite rule)
     * the POST body gets discarded at some point, so for now I am passing the data
     * in via query parameters */
    async function postData(url = '', data = {}) {
      const response = await fetch(url, {
        method: 'POST',
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        redirect: 'follow',
        body: JSON.stringify(data)
      });
      return response.json();
    }

    async function createEventsTable() {
        const query = new URLSearchParams();
        query.append("func", "createEventsTable");
        query.append("test", "1");
        const url = "mt_functions.php?" + query.toString();
        await postData(url, {})
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
        const query = new URLSearchParams();
        query.append("func", "createEvent");
        query.append("test", "1");
        query.append("title", title);
        query.append("desc", desc);
        query.append("location", _location);
        query.append("start_date", start_date);
        query.append("start_time", start_time);
        query.append("end_date", end_date);
        query.append("end_time", end_time);
        const url = "mt_functions.php?" + query.toString();

        // TODO: get the id of the newly created event so it can be deleted
        await postData(url, {})
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error);} );
    }

    async function getEvents() {
        const query = new URLSearchParams();
        query.append('func', 'getEvents');
        query.append("test", "1");
        // TODO: begin and end dates
        const url = 'mt_functions.php?' + query.toString();
        await fetch(url)
            .then(response => response.json())
            .then(data => { console.log(data);} )
            .catch(error => { console.error(error);} );
    }

    async function deleteEvent() {
        const query = new URLSearchParams();
        query.append("func", "deleteEvent");
        query.append("test", "1");
        query.append("event_id", "0");
        const url = "mt_functions.php?" + query.toString();
        await postData(url, {})
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

    async function modifyEvent() {
        const query = new URLSearchParams();
        query.append("func", "modifyEvent");
        query.append("test", "1");
        query.append("event_id", "0");
        // TODO: add parameters
        const url = "mt_functions.php?" + query.toString();
        await postData(url, {})
          .then(data => { console.log(data); } )
          .catch(error => { console.error(error); } );
    }

    async function destroyEventsTable() {
        const query = new URLSearchParams();
        query.append("func", "destroyEventsTable");
        query.append("test", "1");
        const url = "mt_functions.php?" + query.toString();
        await postData(url, {})
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

        await getEvents();

        await deleteEvent();

        await modifyEvent();

        await destroyEventsTable();

    })();

</script>

    </body>

</html>
