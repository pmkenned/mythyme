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

// TODO: Color class

const lightTheme = {
    bgColor:            "white",
    fontColor:          "black",
    lineColor:          "black",
    dashedColor:        "hsl(0, 0%, 70%)",
    eventBrightness:    "100",
    pastColor:          "hsl(0, 0%, 80%)",
    zoomColor:          "hsla(0, 0%, 50%, 0.5)",
    shadowColor:        "hsla(0, 0%, 0%, 0.5)",
};

const darkTheme = {
    bgColor:            "hsl(0, 0%, 20%)",
    fontColor:          "hsl(0, 0%, 80%)",
    lineColor:          "hsl(0, 0%, 40%)",
    dashedColor:        "hsl(0, 0%, 30%)",
    eventBrightness:    "50",
    pastColor:          "hsl(0, 0%, 30%)",
    zoomColor:          "hsla(0, 0%, 100%, 0.5)",
    shadowColor:        "hsla(0, 0%, 100%, 0.5)",
};
