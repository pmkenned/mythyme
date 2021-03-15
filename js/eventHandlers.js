"use strict";

function mousedown(e) {
    currentView.mousedown(e);
}

function mouseup(e) {
    currentView.mousedown(e);
}

function mousemove(e) {
    currentView.mousemove(e);
}

function keydown(e) {

    if (e.key == "?") {
        hotkeys_menu = !hotkeys_menu;

    } else if (e.key == "d") {
        currentView = dayView;
    } else if (e.key == "w") {
        currentView = weekView;
    } else if (e.key == "m") {
        currentView = monthView;
    } else if (e.key == "q") {
        currentView = quarterView;
    } else if (e.key == "y") {
        currentView = yearView;

    } else if (e.key == "D") {
        currentTheme = (currentTheme === darkTheme) ? lightTheme : darkTheme;
        document.body.style.backgroundColor = currentTheme.bgColor;
    } else if (e.key == "r") {
        MyThymeAPI.getEvents();
    } else if (e.key == "u") {
        //test();
    } else if (e.key == "z") {
        fetch('getErrors.php')
          .then(response => response.text())
          .then(data => {console.log(data); });
    } else {
        currentView.keydown(e);
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
    canvas.height = 1.0*window.innerHeight - canvas.offsetTop;
    //canvas.height -= ADDRESS_BAR_HEIGHT; // TODO
    can_w = canvas.width;
    can_h = canvas.height;
    currentView.resize();
    draw();
}
