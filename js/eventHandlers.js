"use strict";

function mousedown(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? true : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? true : mouse_right_btn_down;

    mouse_down_x = clientToCanvasX(e.clientX);
    mouse_down_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        setClickedEvent(mouse_down_x, mouse_down_y);
        if (selected_event === null) {
            if (mouse_down_x > left_col_px && mouse_down_y > top_row_px) {
                new_event_active = true;
                new_event_x_init = mouse_down_x;
                new_event_y_init = mouse_down_y;
                new_event_x_final = mouse_down_x;
                new_event_y_final = mouse_down_y;
                setNewEventStartEnd()
            } else if (mouse_down_x < left_col_px && mouse_down_y > top_row_px) {
                zoom_active  = true;
                zoom_x_init  = mouse_down_x;
                zoom_y_init  = mouse_down_y;
                zoom_x_final = mouse_down_x;
                zoom_y_final = mouse_down_y;
                setZoomStartEnd();
            }
        } else {
            selected_event_x_init = mouse_down_x;
            selected_event_y_init = mouse_down_y;
            selected_event_x_final = mouse_down_x;
            selected_event_y_final = mouse_down_y;
        }
    } else if (mouse_right_btn_down) {
        setClickedEvent(mouse_down_x, mouse_down_y);
        if (selected_event !== null) {
            const new_title = prompt("Rename event", "");
            if (new_title !== null) {
                selected_event.title = new_title;
                MyThymeAPI.modifyEvent(selected_event.id, {title: new_title});
            }
        }
    }
}

function mouseup(e) {
    mouse_left_btn_down = (e.button == LEFT_MOUSE_BUTTON) ? false : mouse_left_btn_down;
    mouse_right_btn_down = (e.button == RIGHT_MOUSE_BUTTON) ? false : mouse_right_btn_down;

    if (e.button == LEFT_MOUSE_BUTTON) {
        if (new_event_active) {
            const dy = new_event_y_final - new_event_y_init;
            if (Math.abs(dy) > MIN_DY) {
                const title = prompt("Enter a title for the event", "New Event");
                if (title !== null) {
                    // TODO: add the event to events[] instead of using getEvents()
                    MyThymeAPI.createEvent(title, "", "",
                        new_event.start_date.getSQLDate(),
                        new_event.start_date.getSQLTime(),
                        new_event.end_date.getSQLDate(),
                        new_event.end_date.getSQLTime());
                }
            }
            new_event_active = false;
        } else if (zoom_active) {
            view_start_hour = zoom_start_hour;
            view_end_hour = zoom_end_hour;
            zoom_active = false;
        } else if (selected_event !== null) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(selected_event);
            if (dest_start_date.getTime() !== selected_event.start_date.getTime() ||
                dest_end_date.getTime() !== selected_event.end_date.getTime()) {
                MyThymeAPI.modifyEvent(selected_event.id, {
                    start_date: dest_end_date.getSQLDate(),
                    start_time: dest_start_date.getSQLTime(),
                    end_date: dest_end_date.getSQLDate(),
                    end_time: dest_end_date.getSQLTime(),
                });

                // actually move the event instead of displaying it offset
                selected_event.start_date.setTime(dest_start_date.getTime());
                selected_event.end_date.setTime(dest_end_date.getTime());
                selected_event_x_final = selected_event_x_init;
                selected_event_y_final = selected_event_y_init;
                calcEventLayers();
            }
        }
        draw();
    } else if (e.button == RIGHT_MOUSE_BUTTON) {
    }
}

function mousemove(e) {

    mouse_x = clientToCanvasX(e.clientX);
    mouse_y = clientToCanvasY(e.clientY);

    if (mouse_left_btn_down) {
        if (new_event_active) {
            new_event_x_final = mouse_x;
            new_event_y_final = mouse_y;
            setNewEventStartEnd();
        } else if (zoom_active) {
            zoom_x_final = mouse_x;
            zoom_y_final = mouse_y;
            setZoomStartEnd();
        } else if (selected_event_moved !== null) {
            selected_event_moved = true;
            selected_event_x_final = mouse_x;
            selected_event_y_final = mouse_y;
        } else {
        }
        draw();
    } else {
        const hoverInfo = checkForEventHover(mouse_x, mouse_y);
        if (hoverInfo.top) {
            canvas.style.cursor = 'n-resize';
        } else if (hoverInfo.bottom) {
            canvas.style.cursor = 'n-resize';
        } else {
            canvas.style.cursor = 'default';
        }
    }
}

function keydown(e) {
    if (e.key == "?") {
        hotkeys_menu = !hotkeys_menu;
    } else if (e.key == "z") {
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
    } else if (e.key == "f") {
        new_event_active = false;
        selected_event = null;
        if (view_start_hour == 0) {
            view_start_hour = 7;
            view_end_hour = 22;
        } else if (view_start_hour == 7) {
            view_start_hour = 16;
            view_end_hour = 21;
        } else {
            view_start_hour = 0;
            view_end_hour = 23;
        }
        resize();
    } else if (e.key == "ArrowDown") {
        if (selected_event !== null) {
            const dest_start_date = selected_event.start_date;
            const dest_end_date = selected_event.end_date;
            dest_start_date.setMinutes(dest_start_date.getMinutes()+grid_size);
            dest_end_date.setMinutes(dest_end_date.getMinutes()+grid_size);
            MyThymeAPI.modifyEvent(selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
        }
    } else if (e.key == "ArrowUp") {
        if (selected_event !== null) {
            const dest_start_date = selected_event.start_date;
            const dest_end_date = selected_event.end_date;
            dest_start_date.setMinutes(dest_start_date.getMinutes()-grid_size);
            dest_end_date.setMinutes(dest_end_date.getMinutes()-grid_size);
            MyThymeAPI.modifyEvent(selected_event.id, {start_time: dest_start_date.getSQLTime(), end_time: dest_end_date.getSQLTime()});
        }
    } else if (e.key == "ArrowLeft") {
    } else if (e.key == "ArrowRight") {
    } else if (e.key == "s") {
        snap_to_grid = !snap_to_grid;
    } else if (e.key == "d") {
        theme = 1 - theme; // TODO: make this robust
        if (theme == DARK_THEME) {
            currentTheme = darkTheme;
            document.body.style.backgroundColor = "hsl(0, 0%, 20%)";
        } else {
            currentTheme = lightTheme;
            document.body.style.backgroundColor = "white";
        }
    } else if (e.key == "r") {
        MyThymeAPI.getEvents();
    } else if (e.key == "n") {
        advanceOriginDate(7);
        MyThymeAPI.getEvents();
    } else if (e.key == "p") {
        advanceOriginDate(-7);
        MyThymeAPI.getEvents();
    } else if (e.key == "t") {
        setOriginDateFromToday();
        MyThymeAPI.getEvents();
    } else if (e.key == "u") {
        //test();
    } else if (e.key == "Escape") {
        selected_event = null;
        hotkeys_menu = false;
    } else if (e.key == "Delete") {
        if (selected_event !== null) {
            MyThymeAPI.deleteEvent(selected_event.id);
        }
    }
    draw();
}

function keyup(e) {
}

function blur() {
}

function contextmenu(e) {
    e.preventDefault();
    return false; // 
}

function resize() {
    canvas.width = window.innerWidth - 2;
    canvas.height = window.innerHeight - canvas.offsetTop;
    //canvas.height -= ADDRESS_BAR_HEIGHT; // TODO
    can_w = canvas.width;
    can_h = canvas.height;
    top_row_px = INIT_TOP_ROW_PX;
    top_row_px += (can_h-top_row_px) - hourHeight()*hours_in_view();
    left_col_px = INIT_LEFT_COL_PX;
    left_col_px += (can_w-left_col_px) - colWidth()*DAYS_IN_WEEK;
    draw();
}
