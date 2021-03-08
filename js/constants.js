"use strict";

const MIN_DY = 0;
const EDGE_SIZE = 10;

const LEFT_MOUSE_BUTTON = 0;
const RIGHT_MOUSE_BUTTON = 2;

const INIT_TOP_ROW_PX = 40;
const INIT_LEFT_COL_PX = 45;

const grid_presets = [1, 5, 10, 15, 20, 30, 60];

const colors = [
    'hsl(0,   50%, 70%)',
    'hsl(30,  50%, 70%)',
    'hsl(60,  50%, 70%)',
    'hsl(90,  50%, 70%)',
    'hsl(120, 50%, 70%)',
    'hsl(150, 50%, 70%)',
    'hsl(180, 50%, 70%)',
    'hsl(210, 50%, 70%)',
    'hsl(240, 50%, 70%)',
    'hsl(270, 50%, 70%)',
    'hsl(300, 50%, 70%)',
    'hsl(330, 50%, 70%)',
];

const LIGHT_THEME = 0;
const DARK_THEME = 1;
