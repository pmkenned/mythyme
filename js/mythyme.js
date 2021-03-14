"use strict";

function draw(timestamp) {
    ctx.clearRect(0, 0, can_w, can_h);

    ctx.fillStyle = currentTheme.bgColor;
    ctx.fillRect(0, 0, can_w, can_h);

    ctx.lineWidth = 1;
    ctx.fillStyle = currentTheme.fontColor;
    ctx.font = "15px Arial";

    // draw horizontal lines
    for (let i = 0; i < hours_in_view(); i++) {

        // draw solid lines
        ctx.strokeStyle = currentTheme.lineColor;
        const hour_y = to_px(top_row_px + i*hourHeight());
        ctx.beginPath();
        ctx.moveTo(0,     hour_y);
        ctx.lineTo(can_w, hour_y);
        ctx.stroke();

        // draw dashed lines
        ctx.strokeStyle = currentTheme.dashedColor;
        ctx.setLineDash([]);
        ctx.setLineDash([3, 1]);
        const grid_per_hour = Math.round(60/grid_size); 
        const grid_height = hourHeight()/grid_per_hour;
        for (let j = 1; j < grid_per_hour; j++) {
            const grid_y = to_px(hour_y + j*grid_height);
            ctx.beginPath();
            ctx.moveTo(left_col_px, grid_y);
            ctx.lineTo(can_w, grid_y);
            ctx.stroke();
        }
        ctx.setLineDash([]);

        // write hour on left-hand side
        const hour = i + view_start_hour;
        const oclock = militaryTo12hr(hour);
        const am_pm = (hour < 12) ? 'am' : 'pm';
        ctx.fillText(`${oclock} ${am_pm}`, 5, top_row_px + i*hourHeight()+15);
    }

    // draw events
    // TODO: draw events that span multiple days
    ctx.lineWidth = 2;
    for (const e of events) {
        if ((e.end_date >= origin_date) && (e.start_date <= next_origin_date)) {
            const [dest_start_date, dest_end_date] = getModifiedTimes(e);

            //const start_col = e.start_date.getDay();
            //const end_col = e.end_date.getDay();
            const start_col = dest_start_date.getDay();
            const top_px = hoursMinsToY(dest_start_date.getHours(), dest_start_date.getMinutes());
            const bot_px = hoursMinsToY(dest_end_date.getHours(),   dest_end_date.getMinutes());

            ctx.fillStyle = e.color;
            const e_color = change_brightness(ctx.fillStyle, currentTheme.eventBrightness);

            const past_color = currentTheme.pastColor;

            ctx.fillStyle = (e.end_date.getTime() < Date.now()) ? past_color : e_color;
            ctx.fillStyle = (e === selected_event) ? "cyan" : ctx.fillStyle;
            ctx.strokeStyle = change_brightness(ctx.fillStyle, 50);
            ctx.roundRect(
                left_col_px + start_col*colWidth()+1 + e.layer*colWidth()*0.1,
                top_px,
                0.9*colWidth() - e.layer*colWidth()*0.1,
                bot_px - top_px, 8,
                true, true
            );

            const longerThan15Min = dest_end_date.getTime() - dest_start_date.getTime() > 1000*60*30;

            ctx.fillStyle = currentTheme.fontColor;
            let px_offset;
            if (longerThan15Min) {
                ctx.font = "bold 20px Arial";
                px_offset = 0;
            } else {
                ctx.font = "bold 14px Arial";
                px_offset = 6;
            }
            ctx.fillText(e.title, left_col_px + (start_col+0.1)*colWidth(), (top_px+bot_px)/2 + px_offset);
            ctx.font = "18px Arial";
            const startEndStr = getStartAndEndTimeString(dest_start_date, dest_end_date);
            if (longerThan15Min) {
                ctx.fillText(startEndStr, left_col_px+(start_col+0.1)*colWidth(), (top_px+bot_px)/2 + 20);
            }
        }
    }

    // draw new event if active
    ctx.fillStyle = "red";
    if (new_event_active) {
        const col = xToCol(new_event_x_init);
        const top_px = hoursMinsToY(new_event.start_date.getHours(), new_event.start_date.getMinutes());
        const bot_px = hoursMinsToY(new_event.end_date.getHours(), new_event.end_date.getMinutes());
        ctx.fillRect(left_col_px + col*colWidth()+1, top_px, colWidth()-1, bot_px - top_px);
    }

    // draw vertical lines and dates across top
    ctx.fillStyle = currentTheme.bgColor;
    ctx.fillRect(0, 0, can_w, top_row_px-1); // white box on top of events

    ctx.lineWidth = 3;
    ctx.strokeStyle = currentTheme.lineColor;
    ctx.beginPath();
    ctx.moveTo(0, to_px(top_row_px));
    ctx.lineTo(can_w, to_px(top_row_px));
    ctx.stroke();

    ctx.fillStyle = currentTheme.fontColor;
    ctx.lineWidth = 1;
    ctx.font = "bold 15px Arial";
    for (let i = 0; i < DAYS_IN_WEEK; i++) {
        ctx.beginPath();
        ctx.moveTo(to_px(left_col_px + i*colWidth()), 0);
        ctx.lineTo(to_px(left_col_px + i*colWidth()), can_h);
        ctx.stroke();
        const col_date = new Date(origin_date.getTime());
        col_date.setDate(col_date.getDate() + i);
        const month = monthNamesShort[col_date.getMonth()];
        const _date = col_date.getDate();
        ctx.fillText(dayNamesShort[i],    to_px(left_col_px + 10 + i*colWidth()), 20);
        ctx.fillText(`${month} ${_date}`, to_px(left_col_px + 10 + i*colWidth()), 40);
    }

    // draw current time
    const current_date = new Date();
    const col = current_date.getDay();
    const line_y = hoursMinsToY(current_date.getHours(), current_date.getMinutes());
    ctx.lineWidth = 3;
    ctx.strokeStyle = "red";
    if (current_date.getTime() >= origin_date.getTime() && current_date.getTime() <= next_origin_date.getTime()) {
        ctx.beginPath();
        ctx.moveTo(to_px(left_col_px + col*colWidth()), to_px(line_y));
        ctx.lineTo(to_px(left_col_px + (col+1)*colWidth()), to_px(line_y));
        ctx.stroke();
    }

    if (zoom_active) {
        ctx.fillStyle = currentTheme.zoomColor;
        const zoom_top_px = hoursMinsToY(zoom_start_hour, 0);
        const zoom_bot_px = hoursMinsToY(zoom_end_hour+1, 0);
        ctx.fillRect(0, zoom_top_px, left_col_px, zoom_bot_px - zoom_top_px);
    }

    if (hotkeys_menu) {
        ctx.fillStyle = currentTheme.shadowColor; 
        ctx.fillRect(0, 0, can_w, can_h);
        ctx.lineWidth = 3;
        ctx.font = "bold 15px Arial";
        ctx.fillStyle = currentTheme.bgColor;
        ctx.roundRect(can_w/3, can_h/3, can_w/3, can_h/3, 8, true, false);
        ctx.fillStyle = currentTheme.fontColor;
        ctx.fillText("Hotkeys", can_w/3+15, can_h/3+30);
    }

    //window.requestAnimationFrame(draw);
}

$(function() {

    CanvasRenderingContext2D.prototype.fillStrokeRect = function(x, y, w, h) {
        this.fillRect(x, y, w, h);
        this.strokeRect(x, y, w, h);
    }

    CanvasRenderingContext2D.prototype.roundRect = roundRect;

    String.prototype.sum = function() {
        let sum = 0;
        for (let i = 0; i < this.length; i++) {
            sum += this.charCodeAt(i);
        }
        return sum;
    }

    String.prototype.lpad = function(padString, length) {
        let str = this;
        while (str.length < length) {
            str = padString + str;
        }
        return str;
    }

    Date.prototype.getSQLDate = function() {
        const d = this;
        const month = `${d.getMonth()+1}`.lpad("0", 2);
        const _date = `${d.getDate()}`.lpad("0", 2);
        return `${d.getFullYear()}-${month}-${_date}`;
    }

    Date.prototype.getSQLTime = function() {
        const d = this;
        const hour = `${d.getHours()}`.lpad("0", 2);
        const min = `${d.getMinutes()}`.lpad("0", 2);
        return `${hour}:${min}:00`;
    }

    Date.prototype.floorN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = floorToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

    Date.prototype.ceilN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = ceilToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

    Date.prototype.roundN = function(n) {
        const orig_min = this.getMinutes();
        const new_min = roundToMultiple(orig_min, n);
        this.setMinutes(new_min);
        return new_min - orig_min;
    }

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

    //draw();
    setInterval(draw, 1000*60); // draw once a minute
    MyThymeAPI.getEvents();
    resize();
    draw();
});
