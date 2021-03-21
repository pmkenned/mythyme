"use strict";

class MyThymeAPI {

    constructor() {
    }

    static checkForUpdates() {
        $.get('mt_functions.php', {func: 'checkForUpdates'})
        .done(function(data) {
            console.log(data);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR.responseJSON);
            MyThymeAPI._reloadIfLoggedOut(jqXHR);
            MyThymeAPI.getEvents();
        });
    }

    static createEvent(eventTitle, eventDescription, eventLocation, startDate, startTime, endDate, endTime) {
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
            MyThymeAPI.getEvents(); // TODO: decide if this is the right way to do this
            draw();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR.responseJSON);
            MyThymeAPI._reloadIfLoggedOut(jqXHR);
            MyThymeAPI.getEvents();
        });
    }

    static getEvents(begin_date, end_date) {
        // TODO: use fetch API
        $.get('mt_functions.php', {
            func: 'getEvents',
            // TODO: avoid accessing private member variables
            begin_date: currentView.origin_date.getSQLDate(),
            end_date: currentView.next_origin_date.getSQLDate()
        })
        .done(function(data) {
            for (const e of data) {
                const start_date = getDateFromSQL(e.start_date, e.start_time);
                const end_date = getDateFromSQL(e.end_date, e.end_time);
                const found_event = events.find(item => item.id === e.id);
                //const color = eventColors[e.id % eventColors.length];
                const color = eventColors[e.title.sum() % eventColors.length];
                if (found_event === undefined) {
                    events.push({title: e.title, start_date: start_date, end_date: end_date, color: color, id: e.id, layer: 0 });
                } else {
                    Object.assign(found_event , {title: e.title, start_date: start_date, end_date: end_date, color: color, id: e.id});
                }
            }
            currentView._calcEventLayers(); // TODO: separation of concerns?
            draw();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR.responseJSON);
            MyThymeAPI._reloadIfLoggedOut(jqXHR);
        });
    }

    static deleteEvent(eventID) {

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
            currentView._calcEventLayers(); // TODO
            //MyThymeAPI.getEvents();
            draw();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR.responseJSON);
            MyThymeAPI._reloadIfLoggedOut(jqXHR);
            MyThymeAPI.getEvents();
        });
    }

    static modifyEvent(eventID, fields) {
        $.post('mt_functions.php', {
            func: 'modifyEvent',
            event_id: eventID,
            ...fields
        }).done(function(data) {
            console.log(data);
            currentView._calcEventLayers(); // TODO
            draw();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error(jqXHR.responseJSON);
            MyThymeAPI._reloadIfLoggedOut(jqXHR);
            MyThymeAPI.getEvents();
        });
    }

    static _reloadIfLoggedOut(jqXHR) {
        if (jqXHR.responseJSON === "ERROR: username not defined") {
            console.log('refreshing...');
            window.location.reload();
        }
    }
}
