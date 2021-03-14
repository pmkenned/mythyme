"use strict";

let top_row_px = INIT_TOP_ROW_PX;
let left_col_px = INIT_LEFT_COL_PX;

let grid_idx = 3;
let grid_size = grid_presets[grid_idx];

let $canvas;
let canvas;
let ctx;
let can_w, can_h;

let snap_to_grid = true;

let origin_date;
let next_origin_date;

let mouse_x, mouse_y;
let mouse_down_x, mouse_down_y;

let mouse_left_btn_down = false;
let mouse_right_btn_down = false;

let selected_event = null;
let selected_top = false;
let selected_bot = false;
let selected_event_moved = false;
let selected_event_x_init, selected_event_y_init;
let selected_event_x_final, selected_event_y_final;

let new_event_active = false;
let new_event = {};
let new_event_x_init, new_event_y_init;
let new_event_x_final, new_event_y_final;

let zoom_active = false;
let zoom_x_init,  zoom_y_init;
let zoom_x_final, zoom_y_final;
let zoom_start_hour, zoom_end_hour;

let hotkeys_menu = false;

let currentTheme = lightTheme;

let events = [];
