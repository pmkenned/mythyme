"use strict";

function draw(timestamp) {
    currentView.render();
}

$(function() {

    $canvas = $('#myCanvas');
    canvas = $canvas[0];
    ctx = canvas.getContext('2d');
    setOriginDateFromToday();

    $(window).mousedown(mousedown);
    $(window).mouseup(mouseup);
    $(window).mousemove(mousemove);
    $(window).keydown(keydown);
    $(window).keyup(keyup);
    $(window).blur(blur);
    $(window).contextmenu(contextmenu);
    $(window).resize(resize);

    currentView = weekView;

    //draw();
    setInterval(draw, 1000*60); // draw once a minute
    MyThymeAPI.getEvents();
    resize();
    draw();
});
