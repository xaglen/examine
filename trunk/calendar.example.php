<?php
require_once 'HTML/QuickForm.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<title>HTML QuickForm Dates + YUI Control</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script type="text/javascript" src="yui/build/yahoo/yahoo.js"></script>
<script type="text/javascript" src="yui/build/dom/dom.js"></script>
<script type="text/javascript" src="yui/build/event/event.js"></script>
<script type="text/javascript" src="yui/build/calendar/calendar.js"></script>
<script type="text/javascript">
<!--
YAHOO.util.Event.addListener(window, 'load', function(e) {
if (!(document.createElement && document.getElementsByTagName)) return;
// first thing's first: create a stylesheet and attach it to the <head>
var ss = document.createElement('link');
ss.setAttribute('rel', 'stylesheet');
ss.setAttribute('type', 'text/css');
ss.setAttribute('href', 'yui/build/calendar/assets/calendar.css');
document.getElementsByTagName('head')[0].appendChild(ss);

// create a global variable to contain my calendars, similar to the YAHOO namespace to avoid future collisions
window._jcp_ = (window._jcp_ ? window._jcp_ : {});
window._jcp_.calendarControls = (window._jcp_.calendarControls ?
window._jcp_.calendarControls : []);
// grab all of my HTML_QuickForm date controls into one array
var dateControls = YAHOO.util.Dom.getElementsByClassName('control-date');
/**
* main loop:
* we'll determine the form element that's the x-great grandparent of
the date control
* once we find the form element, we'll
*/
var forms = [];
while(dateControls.length > 0) {
var p = dateControls[0].parentNode;
// grab the name of the control (minus the ending parts) for later use
var n = dateControls[0].getAttribute('name').replace(/\[.*?\]$/, '');
var ds = p.getElementsByTagName('select');
var currentMonth = ds[1].value + "/" + ds[2].value;
var currentDate = ds[1].value + "/" + ds[0].value + "/" + ds[2].value;

// now that we've got our values, we can remove all of the children of the parent node (we'll attach some new ones later)
while(p.firstChild) p.removeChild(p.firstChild);

// determine the parent form of this control
var f = p.parentNode;
while(f.nodeName.toLowerCase() != 'form') {
if (f.nodeName.toLowerCase() == 'body') return; // we'll want to exit the loop if we don't find a good node -- this assume's you're using valid pages that actually have a <body> present
f = f.parentNode;
}

// create a container for our calendar and attach it to the parent node
var calContainer = document.createElement('div');
calContainer.setAttribute('id', n+"_container");
p.appendChild(calContainer);
// create a new calendar control
var index = window._jcp_.calendarControls.length;
window._jcp_.calendarControls[index] = new YAHOO.widget.Calendar(n, n+"_container", currentMonth, currentDate);
window._jcp_.calendarControls[index]._jcp_ = { "parentForm": f };

// since we're working with all of the forms here, we'll want to avoid attaching multiple onsubmits to the same object
// so we search for it and flag it if we find it
var foundForm = false;
for (var formIndex in forms) {
if (forms[formIndex] == f) {
foundForm = true;
break;
}
}
if (! foundForm) {
// we didn't find it, so we'll attach an onsubmit function to this form that'll add some hidden fields to take care of the selects we eliminated earlier
YAHOO.util.Event.addListener(f, 'submit', function(ev) {
var index = 0;
for (index = 0; index < window._jcp_.calendarControls.length; index++) {
if (window._jcp_.calendarControls[index]._jcp_.parentForm == this) {
for (var d in window._jcp_.calendarControls[index].getSelectedDates()) {
var h1 = document.createElement('input');
h1.setAttribute('name', window._jcp_.calendarControls[index].id + '[d]');
h1.setAttribute('type', 'hidden');
h1.setAttribute('value',
window._jcp_.calendarControls[index].getSelectedDates()[d].getDate());
this.appendChild(h1);
var h2 = document.createElement('input');
h2.setAttribute('name', window._jcp_.calendarControls[index].id + '[M]');
h2.setAttribute('type', 'hidden');
h2.setAttribute('value',
(window._jcp_.calendarControls[index].getSelectedDates()[d].getMonth()+1));
this.appendChild(h2);
var h3 = document.createElement('input');
h3.setAttribute('name', window._jcp_.calendarControls[index].id + '[Y]');
h3.setAttribute('type', 'hidden');
h3.setAttribute('value',
window._jcp_.calendarControls[index].getSelectedDates()[d].getFullYear());
this.appendChild(h3);
}
}
}
});
}
// refetch the HTML_QuickForm date controls (since we removed three-at-a-time)
dateControls = YAHOO.util.Dom.getElementsByClassName('control-date');
}
// finally, render all of our controls
for (var calIndex in window._jcp_.calendarControls) {
window._jcp_.calendarControls[calIndex].render();
}
});
-->
</script>
</head>
<body>
<h1>Incoming form values</h1>
<pre>
<?php
print_r($_POST);
?>
</pre>
<?php
$form = new HTML_QuickForm('blah');
$form->addElement('date', 'startDate', 'Start Date', null,
array('class'=>'control-date'));
$form->addElement('date', 'endDate', 'End Date', null,
array('class'=>'control-date'));
$form->addElement('submit', 'btnSubmit', 'Submit');
$form->setDefaults(array(
'startDate' => date('d-M-Y'),
'endDate' => date('d-M-Y')
)); // default the date control to today's date
$form->display();
?>
</body>
</html>