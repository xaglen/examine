/*
from http://blog.davglass.com/files/yui/cal2/more.php

<form method="get" action="more.php">
    Select Date 1: <input type="text" name="cal1Date" id="cal1Date" autocomplete="off" size="35" value="" /><br>
    Select Date 2: <input type="text" name="cal1Date2" id="cal1Date2" autocomplete="off" size="35" value="" /><br>
    Select Date 3: <input type="text" name="cal1Date3" id="cal1Date3" autocomplete="off" size="35" value="" /><br>
    <input type="submit" value="Submit" />
</form>
<div id="cal1Container"></div>
        
*/
var cal1;
var over_cal = false;
var cur_field = '';
function init() {
    cal1 = new YAHOO.widget.Calendar("cal1","cal1Container");
    cal1.selectEvent.subscribe(getDate, cal1, true);
    cal1.renderEvent.subscribe(setupListeners, cal1, true);
    YAHOO.util.Event.addListener(['begin_date', 'end_date'], 'focus', showCal);
    YAHOO.util.Event.addListener(['begin_date', 'end_date'], 'blur', hideCal);
    cal1.render();
}

function setupListeners() {
    YAHOO.util.Event.addListener('cal1Container', 'mouseover', overCal);
    YAHOO.util.Event.addListener('cal1Container', 'mouseout', outCal);
}

function getDate() {
        var calDate = this.getSelectedDates()[0];
        calDate = (calDate.getMonth() + 1) + '/' + calDate.getDate() + '/' + calDate.getFullYear();
        cur_field.value = calDate;
        over_cal = false;
        hideCal();
}

function showCal(ev) {
    var tar = YAHOO.util.Event.getTarget(ev);
    cur_field = tar;
    var xy = YAHOO.util.Dom.getXY(tar);
    var date = YAHOO.util.Dom.get(tar).value;
    if (date) {
        cal1.cfg.setProperty('selected', date);
        cal1.cfg.setProperty('pagedate', new Date(date), true);
        cal1.render();
    } else {
        cal1.cfg.setProperty('selected', '');
        cal1.cfg.setProperty('pagedate', new Date(), true);
        cal1.render();
    }
    YAHOO.util.Dom.setStyle('cal1Container', 'display', 'block');
    xy[1] = xy[1] + 20;
    YAHOO.util.Dom.setXY('cal1Container', xy);
}

function hideCal() {
    if (!over_cal) {
        YAHOO.util.Dom.setStyle('cal1Container', 'display', 'none');
    }
}

function overCal() {
    over_cal = true;
}

function outCal() {
    over_cal = false;
}

YAHOO.util.Event.addListener(window, 'load', init);
