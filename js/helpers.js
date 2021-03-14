"use strict";

const HOURS_IN_DAY = 24;
const DAYS_IN_WEEK = 7;

const dayNamesShort = [
    'Sun',
    'Mon',
    'Tue',
    'Wed',
    'Thu',
    'Fri',
    'Sat'
];

const dayNames = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday'
];

const monthNames = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
];

const monthNamesShort = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec'
];

/**
 * Draws a rounded rectangle using the current state of the canvas.
 * If you omit the last three params, it will draw a rectangle
 * outline with a 5 pixel border radius
 * @param {CanvasRenderingContext2D} ctx
 * @param {Number} x The top left x coordinate
 * @param {Number} y The top left y coordinate
 * @param {Number} width The width of the rectangle
 * @param {Number} height The height of the rectangle
 * @param {Number} [radius = 5] The corner radius; It can also be an object 
 *                 to specify different radii for corners
 * @param {Number} [radius.tl = 0] Top left
 * @param {Number} [radius.tr = 0] Top right
 * @param {Number} [radius.br = 0] Bottom right
 * @param {Number} [radius.bl = 0] Bottom left
 * @param {Boolean} [fill = false] Whether to fill the rectangle.
 * @param {Boolean} [stroke = true] Whether to stroke the rectangle.
 */
function roundRect(x, y, width, height, radius, fill, stroke) {
    if (typeof stroke === 'undefined') {
        stroke = true;
    }
    if (typeof radius === 'undefined') {
        radius = 5;
    }
    if (typeof radius === 'number') {
        radius = {tl: radius, tr: radius, br: radius, bl: radius};
    } else {
        var defaultRadius = {tl: 0, tr: 0, br: 0, bl: 0};
        for (var side in defaultRadius) {
            radius[side] = radius[side] || defaultRadius[side];
        }
    }
    this.beginPath();
    this.moveTo(x + radius.tl, y);
    this.lineTo(x + width - radius.tr, y);
    this.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
    this.lineTo(x + width, y + height - radius.br);
    this.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
    this.lineTo(x + radius.bl, y + height);
    this.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
    this.lineTo(x, y + radius.tl);
    this.quadraticCurveTo(x, y, x + radius.tl, y);
    this.closePath();
    if (fill) {
        this.fill();
    }
    if (stroke) {
        this.stroke();
    }
}

CanvasRenderingContext2D.prototype.fillStrokeRect = function(x, y, w, h) {
    this.fillRect(x, y, w, h);
    this.strokeRect(x, y, w, h);
}

CanvasRenderingContext2D.prototype.roundRect = roundRect;

function getDateFromSQL(sqlDate, sqlTime) {
    let [year, month, _date] = sqlDate.split('-').map(Number);
    month--; // NOTE: JavaScript's Date class indexes months starting at 0
    const [hour, min] = sqlTime.split(':').map(Number);
    return new Date(year, month, _date, hour, min);
}

const clientToCanvasY = y => y - canvas.offsetTop;
const clientToCanvasX = x => x;
const to_px = x => Math.round(x) + 0.5;
const floorToMultiple = (x,n) => Math.floor(x/n)*n;
const ceilToMultiple = (x,n) => Math.ceil(x/n)*n;
const roundToMultiple = (x,n) => Math.round(x/n)*n;
const militaryTo12hr = hour => (hour == 0) ? 12 : ((hour > 12) ? hour-12 : hour);
const minutesBetweenDates = (dt1, dt2) => Math.round((dt2.getTime() - dt1.getTime()) / (60*1000));

const getStartAndEndTimeString = (sdate, edate) => {
    let start_time = `${militaryTo12hr(sdate.getHours())}`;
    if (sdate.getMinutes() != 0) {
        start_time += `:${sdate.getMinutes().toString().lpad("0",2)}`;
    }
    const s_ampm = (sdate.getHours() < 12) ? 'am' : 'pm';
    let end_time = `${militaryTo12hr(edate.getHours())}`;
    if (edate.getMinutes() != 0) {
        end_time += `:${edate.getMinutes().toString().lpad("0",2)}`;
    }
    const e_ampm = (edate.getHours() < 12) ? 'am' : 'pm';
    return (s_ampm === e_ampm) ? `${start_time} - ${end_time}${e_ampm}` : `${start_time}${s_ampm} - ${end_time}${e_ampm}`;
};

const parseInt16 = s => parseInt(s, 16);
// TODO: use HSL
const change_brightness = (color, percent) => {
    const r = Math.min(Math.round(parseInt16(color.substr(1,2)) * percent/100.0), 255);
    const g = Math.min(Math.round(parseInt16(color.substr(3,2)) * percent/100.0), 255);
    const b = Math.min(Math.round(parseInt16(color.substr(5,2)) * percent/100.0), 255);
    const rgb = r.toString(16).lpad("0", 2) + g.toString(16).lpad("0", 2) + b.toString(16).lpad("0", 2);
    return ("#" + rgb);
};

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
