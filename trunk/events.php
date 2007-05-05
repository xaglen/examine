<?php
/**
 * This file displays and processes events
 *
 * @package examine
 * @subpackage interface
 * @todo - fix data entry to be default display and show only desired fields
 */
require_once 'config.php';
require_once 'includes/authentication_header.php';
require_once 'HTML/QuickForm.php';
//require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
require_once 'HTML/QuickForm/Renderer/Tableless.php';
require_once 'includes/functions.php';
require_once 'includes/functions.time.php';

$db=createDB();

// if event_id is specified in GET or POST, extract it here
if (isset($_REQUEST['event_id'])) {
	$event_id=$_REQUEST['event_id'];
}

if (!isset($_POST['ACTION']) && !isset($_GET['action'])) {
    $total=$db->getOne('SELECT COUNT(*) FROM events e, ministry_people mp WHERE mp.pid='.$a->getPid.' AND mp.role_id<=2 AND e.ministry_id=mp.ministry_id'); //role_id of 1 and 2 indicate staff - higher is student or misc
    if ($total>0) {
        $_POST['ACTION']='DEFAULT';
		$_GET['action']='DEFAULT';
    } else {
        $_GET['action']='add';
    }
}

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
	case 'add': // create a blank form for data entry
		$event_id=NULL;
		// this should print out a blank form for data entry
		$sql='DESCRIBE events';
		$result=$db->query($sql);
		while ($row=$result->fetchRow()) {
			$event[$row[0]]='';
		}
		unset($event['event_id']); // we don't want the user to enter a value for this
		reset($event);
	break;
	case 'delete': // request confirmation for an event deletion
		echo "Are you sure you want to delete this event? There is NO UNDO!<br/>";
		echo '<FORM ACTION="'.$_SERVER['PHP_SELF'].'"><INPUT TYPE="SUBMIT" NAME="ACTION" VALUE="CONFIRM"><INPUT TYPE="HIDDEN" NAME="event_id" VALUE="'.$event_id.'"></FORM>';
	exit();
	default:
	}
}

switch ($_POST['ACTION']) {
case 'CONFIRM': // remove an event from the database
	if (ownsEvent($user_id,$event_id)) {
		$sql='DELETE FROM events WHERE event_id='.$event_id;
		$db->exec($sql);
		$sql='DELETE FROM eventattendance WHERE event_id='.$event_id;
		$db->exec($sql);
	}
	break;
case 'UPDATE': // process modifications to an event
	unset($_POST['ACTION']);
	if (ownsEvent($user_id,$event_id)) {
		$db->autoExecute('events',$_POST,MDB2_AUTOQUERY_UPDATE,"event_id='$event_id'");
	} else {
		echo "You do not have authority to modify this event.</br>";
	}
	break;
case 'INSERT': // this takes the results of ADD and puts it in the database
	unset($_POST['ACTION']);
    $event_id=$db->nextID();
    $_POST['event_id']=$event_id;
	if (isset($_POST['pid'])) {
		$pids=$_POST['pid'];
		foreach($pids as $pid) {
			$sql="INSERT INTO event_attendance SET event_id='$event_id',pid='$pid'";
			$db->exec($sql);
		}
		unset($_POST['pid']);
	}
	$db->autoExecute('events',$_POST,MDB2_AUTOQUERY_INSERT);
	break;
default:
}

if (!isset($event_id)) {
	$event_id=NULL;
	$name='All Events';
} else {
	$sql='SELECT *,UNIX_TIMESTAMP(begin) as unixdate FROM events WHERE event_id='.$event_id;
	$result=$db->query($sql);
	$event=$result->fetchRow();
	$name=sprintf('%s %s',$event['name'],date('F jS, Y',$event['unixdate']));
}
?>
<html>
<head>
<title><?php echo $name;?></title>
<link rel="stylesheet" href="examine.css" type="text/css">
<link rel="stylesheet" href="quickform.css" type="text/css">
<script type="text/javascript" src="datarequestor-1.6.js"></script>
<script type="text/javascript" src="display.js"></script>
<script type="text/javascript" src="forms.js"></script>
<script type="text/javascript">
function SetDate(HiddenFieldID,NewDate,NewDateID) {
	if (!document.getElementById) return null;
	dateField = document.getElementById(HiddenFieldID);
	dateField.value=NewDate;
	for (i=0;i<31;i++) {
		calendarEntry=document.getElementById('cal'+i);
		if (calendarEntry) calendarEntry.className='normalDate';
	}
	newDateElement = document.getElementById(NewDateID);
	newDateElement.className='selectedDate';
}

function ChangeCalendar(datefield,month,day,year) {
	if (!document.getElementById) return null;
	var cal = new DataRequestor();
	cal.setObjToReplace("eventcalendar");
	cal.addArg(_GET,"datefield",datefield);
	cal.addArg(_GET,"month",month);
	cal.addArg(_GET,"day",day);
	cal.addArg(_GET,"year",year);
	cal.getURL("subforms/monthly.calendar.php");
}
</script>
<style type="text/css">
.selectedDate {
	background-color: red;
}
.normalDate {
	background-color: white;
}
</style>
</head>
<body>
<?php include 'templates/header.php';?>
<div id="main">
<div id="sidebar">
<h3>Help</h3>
</div>
<?php
if ($event_id===NULL && $_GET['action']!='ADD') {
	$sql='SELECT event_id,name,begin,UNIX_TIMESTAMP(begin) as unixdate FROM events e, ministry_people mp WHERE mp.pid='.$a->getPid().' AND mp.role_id<=2 AND e.ministry_id=mp.ministry_id ORDER BY begin DESC';
	$result=$db->query($sql);
	$OldTimeLabel='';
	echo '<ol>';
	while ($row=$result->fetchRow()) {
		$TimeLabel=getTimeLabel($row['unixdate']);
		if ($TimeLabel!=$OldTimeLabel) {
			$OldTimeLabel=$TimeLabel;
			echo "</ol><H2>$TimeLabel</H2>\n";
			echo "<ol>\n";
		}
		printf('<li><a href="%s?event_id=%s">%s %s</a></li>',$_SERVER['PHP_SELF'],$row['event_id'],$row['name'],date('F jS, Y',$row['unixdate']));

	}
	echo "</ol></ul>";
} else { // event_id is not equal to null or ADD is set
	echo '<span class="actions"><a href="#" onclick="javascript:editmode()">edit</a> | <a href='.$_SERVER['PHP_SELF'].'?action=delete&amp;event_id='.$event_id.'>delete</a> | <a href='.$_SERVER['PHP_SELF'].'?action=add>add a new event</a></span><br/>';
     $form = new HTML_QuickForm('add','POST',$_SERVER['PHP_SELF'],null,null,true);
     $form->addElement('header','','Event');
	echo '<em>This was '.readableTimeDiff($event['unixdate'],time()).'</em><br/>';
	echo 'Attendance: '.$event['estimated_attendance'].'&nbsp; ('.getEventAttendance($event_id).' signed in)<br/>';

	// add a file admin/options.events.php which will allow you to set global events options
$eventFieldsToDisplay=deserialize(getUserPreference($a->getPid(),'eventFieldsToDisplay'));

if (!$eventFieldsToDisplay) {
	$eventFieldsToDisplay=deserialize(getSystemVariable('eventFieldsToDisplay'));
}

unset($event['unixdate']);

// maybe change this so that we check two things: if the field is set to display by default AND whether or not it is null
if (!$eventFieldsToDisplay) { // if neither the user pref nor the system variable is set
	$visibleFields=$eventFields;
} else {
	$visibleFields=array_intersect_key($event,$eventFieldsToDisplay);
	$hiddenFields=array_diff_key($event,$eventFieldsToDisplay); // perhaps not necessary using this implementation
}

foreach($visibleFields as $field) {
	switch($field) {
		case 'notes':
			$form->addElement('textarea',$field,$field);
			break;
		case 'begin':
		case 'end':
			$form->addElement('hidden',$field,$field);
			if (!$event[$field]) {
				$event[$field]=date('Y-m-d 00:00:00',time());
			}
			// need to find a better calendar widget
			$calendar=<<<CALENDAR
			<div id="eventcalendar">
			</div>
			<script type="text/javascript">
			var cal = new DataRequestor();
			cal.setObjToReplace("eventcalendar");
			cal.addArg(_GET,"datefield","<?php echo $field;?>");
			cal.addArg(_GET,"month",<?php echo date('m',strtotime($event[$field']))?>);
			cal.addArg(_GET,"day",<?php echo date('j',strtotime($event[$field']))?>);
			cal.addArg(_GET,"year",<?php echo date('Y',strtotime($event[$field']))?>);
			cal.getURL("subforms/monthly.calendar.php");
			</script>
CALENDAR;
			$form->addElement('html',$calendar);
			break;
		default:
			$form->addElement('text',$field,$field);
		}
}
foreach ($hiddenFields as $field) {
	switch ($field) {
		default:
			$form->addElement('hidden',$field,$field);
	}
}
	$form->setDefaults($event);
	$form->applyFilter('__ALL__','trim');
	//$form->display();
	$renderer =& new HTML_QuickForm_Renderer_Tableless();
	$form->accept($renderer);
	echo $renderer->toHtml();
	echo '<a href="#">view all possible fields</a></div>';
	if ($_GET['action']=='add') {
		echo "<H2>Regulars Who Might Have Been There</H2>\n";
		include('subforms/event.regulars.php');
		echo '<INPUT TYPE="SUBMIT" NAME="ACTION" VALUE="INSERT">';
	} else {
		echo '<INPUT TYPE="SUBMIT" NAME="ACTION" VALUE="UPDATE">';
	}
	?>
	</form></div>
	<?php
} // end if $event_id===NULL
// display those present -> do this as a javascript replace to allow for dynamic updating
if ($event_id!==NULL) {
    ?>
        <div id="eventattenders" class="subform">
        </div>
        <script type="text/javascript">
        var req = new DataRequestor();
    req.setObjToReplace('eventattenders');
    req.addArg(_GET, "event_id", "<?php echo $event_id;?>");
    req.getURL('subforms/event.attendance.php');
	
    </script>
        <?php
} // end $event_id !==NULL
?>
</div>
<?php include 'templates/footer.php';?>
</body>
</html>
