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
require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
require_once 'HTML/QuickForm/Renderer/Tableless.php';
require_once 'includes/functions.php';
require_once 'includes/functions.time.php';

$db=createDB();
$message='';


// if event_id is specified in GET or POST, extract it here
if (array_key_exists('event_id', $_REQUEST) && ctype_digit($_REQUEST['event_id'])) {
	$event_id=$_REQUEST['event_id'];
}

// if being run on a blank database then default to add new data - maybe this should be checked later when listing all events (check if size of array is zero)...
if (array_key_exists('action',$_REQUEST)) {
		$action=$_REQUEST['action'];
	} else {
		$total=$db->getOne('SELECT COUNT(*) FROM events e, ministry_people mp WHERE mp.pid='.$a->getPid().' AND mp.role_id<=2 AND e.ministry_id=mp.ministry_id'); //role_id of 1 and 2 indicate staff - higher is student or misc
		if ($total==0) {
			$_REQUEST['action']='add';
			$action='add';
		}
}

if (isset($action)) {
	switch ($action) {
	case 'add': // create a blank form for data entry
		$event_id=NULL;
		// this should print out a blank form for data entry
		$result=$db->query('DESCRIBE events');
		while ($row=$result->fetchRow()) {
			$event[$row['Field']]='';
		}
		unset($event['event_id']); // we don't want the user to enter a value for this
		reset($event);
	break;
	case 'delete': // request confirmation for an event deletion - maybe I should do this via javascript?
		//echo "Are you sure you want to delete this event? There is NO UNDO!<br/>";
		//echo '<FORM action="'.$_SERVER['PHP_SELF'].'"><INPUT TYPE="SUBMIT" NAME="action" VALUE="CONFIRM"><INPUT TYPE="HIDDEN" NAME="event_id" VALUE="'.$event_id.'"></FORM>';
//	exit();
	if (ownsEvent($a->getUserId(),$event_id)) {
		$sql='DELETE FROM events WHERE event_id='.$event_id;
		$db->exec($sql);
		$sql='DELETE FROM eventattendance WHERE event_id='.$event_id;
		$db->exec($sql);
		$message.='Event deleted.';
		unset($event_id);
	}
	break;
	case 'UPDATE': // process modifications to an event
		unset($_POST['action']);
		unset($_POST['_qf__add']);
		unset($_POST['btnSave']);
		$_POST['begin']=gmdate("Y-m-d H:i:s", strtotime($_POST['begin_date'].' '.$_POST['begin_time']));
		$_POST['end']=gmdate("Y-m-d H:i:s", strtotime($_POST['end_date'].' '.$_POST['end_time']));
		unset($_POST['begin_date']);
		unset($_POST['begin_time']);
		unset($_POST['end_date']);
		unset($_POST['end_time']);
		if (ownsEvent($a->getUserId(),$event_id)) {
			$db->autoExecute('events',$_POST,MDB2_AUTOQUERY_UPDATE,"event_id='$event_id'");
			$message.='Event updated.';
		} else {
			$message.='You do not have authority to modify this event.';
		}
	break;
	case 'INSERT': // this takes the results of ADD and puts it in the database
		unset($_POST['action']);
		unset($_POST['_qf__add']);
		unset($_POST['btnSave']);
		$_POST['begin']=gmdate("Y-m-d H:i:s", strtotime($_POST['begin_date'].' '.$_POST['begin_time']));
		$_POST['end']=gmdate("Y-m-d H:i:s", strtotime($_POST['end_date'].' '.$_POST['end_time']));
		unset($_POST['begin_date']);
		unset($_POST['begin_time']);
		unset($_POST['end_date']);
		unset($_POST['end_time']);
		$event_id=$db->nextID('events');
		$_POST['event_id']=$event_id;
		if (array_key_exists('pid',$_POST) && is_array($_POST['pid'])) {
			foreach($_POST['pid'] as $pid) {
				$sql="INSERT INTO event_attendance SET event_id='$event_id',pid='$pid'";
				$db->exec($sql);
			}
			unset($_POST['pid']);
		}
		$db->autoExecute('events',$_POST,MDB2_AUTOQUERY_INSERT);
		$message.='Event added.';
		break;
	default:
		$message.="I'm sorry - I don't understand what you want me to do.";
}
}

if (isset($event_id)) {
	$sql='SELECT *,UNIX_TIMESTAMP(begin) as unixdate FROM events WHERE event_id='.$event_id;
	$result=$db->query($sql);
	$event=$result->fetchRow();
	$name=sprintf('%s %s',$event['name'],date('F jS, Y',$event['unixdate']));
} else {
	$event_id=NULL;
	$name='All Events';
}
?>
<html>
<head>
<title><?php echo $name;?></title>
<link rel="stylesheet" href="css/examine.css" type="text/css">
<link rel="stylesheet" href="css/tabs.css" type="text/css">
<link rel="stylesheet" href="css/quickform.css" type="text/css">
<link type="text/css" rel="stylesheet" href="yui/calendar/assets/calendar.css">
<link rel="stylesheet" href="modalbox/modalbox.css" type="text/css">
<!-- <link type="text/css" rel="stylesheet" href="http://yui.yahooapis.com/2.2.2/build/logger/assets/logger.css"> -->
</head>
<body>
<?php //<script type="text/javascript" src="datarequestor-1.6.js"></script> ?>
<script type="text/javascript" src="scriptaculous-js-1.7.0/lib/prototype.js"></script>
<script type="text/javascript" src="scriptaculous-js-1.7.0/src/scriptaculous.js"></script>
<script type="text/javascript" src="modalbox/modalbox.js"></script>
<script type="text/javascript" src="forms.js"></script>
<script type="text/javascript" src="yui/yahoo/yahoo.js"></script>
<script type="text/javascript" src="yui/event/event-min.js"></script>
<script type="text/javascript" src="yui/utilities/utilities.js"></script>
<script type="text/javascript" src="yui/dom/dom-min.js"></script>
<script type="text/javascript" src="yui/calendar/calendar-min.js"></script>
<?php
/*
<script type="text/javascript" src="yui/logger/logger-min.js"></script>
<script type="text/javascript"> 
var myLogReader = new YAHOO.widget.LogReader(); 
*/
?>
<script type="text/javascript">

var cal1;
var over_cal = false;
var cur_field = '';

function setupCal1() {
    cal1 = new YAHOO.widget.Calendar("cal1","cal1Container");
    cal1.selectEvent.subscribe(getDate, cal1, true);
    cal1.renderEvent.subscribe(setupListeners, cal1, true);
    //YAHOO.util.Event.addListener('begin_date', 'focus', showCal);
	//YAHOO.util.Event.addListener('end_date', 'focus', showCal);
    //YAHOO.util.Event.addListener('begin_date', 'blur', hideCal);
	//YAHOO.util.Event.addListener('end_date', 'blur', hideCal);
	YAHOO.util.Event.addListener(['begin_date', 'end_date'], 'focus', showCal);
    YAHOO.util.Event.addListener(['begin_date', 'end_date'], 'blur', hideCal);
 //   cal1.render();
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

YAHOO.util.Event.addListener(window, 'load', setupCal1);
</script>
<?php include 'templates/header.php';?>
<div id="main">
<div id="sidebar">
<h3>Help</h3>
</div>

<p id='statusmsg'><?php echo $message;?></p>
<script type='text/javascript'>new Effect.Highlight('statusmsg', {duration: 3.0});</script>
	<div class="buttons">
<?php
if ((isset($action) && $action=='add') || $event_id!==null) {
?>
	<a href="events.php">
	<img src="<?php echo $rooturl.'/graphics/icons/text_list_bullets.png';?>" height="16" width="16"/>
	List All Events
	</a>
	<?php
}

if (!isset($action) || $action!=='add') {
	?>
	<a class="positive" href="events.php?action=add">
	<img src="<?php echo $rooturl.'/graphics/icons/add.png';?>" height="16" width="16"/>
	Add Event
	</a>	
	<?php
}

if (isset($event_id)) {
	?>
	<a class="negative" href="events.php?action=delete?event_id=<?php echo $event_id;?>" onclick="javascript:return confirm('Are you sure you want to delete this event?')">
	<img src="<?php echo $rooturl.'/graphics/icons/cancel.png';?>" height="16" width="16"/>
	Delete Event
	</a>	
	<?php
}
?>
</div> <!-- end of buttons -->
<br/> 
<?php
if (isset($event_id)) {
		echo '<em>This was '.readableTimeDiff($event['unixdate'],time()).'</em><br/>';
		unset($event['unixdate']);
		echo 'Attendance: '.$event['estimated_attendance'].'&nbsp; ('.getEventAttendance($event_id).' signed in)<br/>';
	}

if ($event_id===NULL && !isset($action)) {
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
} else { // event_id is not equal to null or we are adding an event
	//echo '<span class="actions"><a href="#" onclick="javascript:editmode()">edit</a> | <a href='.$_SERVER['PHP_SELF'].'?action=delete&amp;event_id='.$event_id.' onclick="javascript:return confirm(\'Are you sure you want to delete this module?\')">delete</a> | <a href='.$_SERVER['PHP_SELF'].'?action=add>add a new event</a></span><br/>';
$form = new HTML_QuickForm_DHTMLRulesTableless('add','POST',$_SERVER['PHP_SELF'],null,null,true);
$form->addElement('header','','');
// $form->addElement('html','<div id="cal1Container"></div>'); // used later for YUI calendar
	   
// add a file admin/options.events.php which will allow you to set global events options
$eventFieldsToDisplay=$a->getPreference('eventFieldsToDisplay');
if (!$eventFieldsToDisplay) {
	$eventFieldsToDisplay=getSystemVariable('eventFieldsToDisplay');
}

// maybe change this so that we check two things: if the field is set to display by default AND whether or not it is null
if (!$eventFieldsToDisplay) { // if neither the user pref nor the system variable is set
	$visibleFields=array_keys($event);
} else {
	$eventFieldsToDisplay=unserialize($eventFieldsToDisplay);
	$visibleFields=array_intersect_key($event,$eventFieldsToDisplay);
	$hiddenFields=array_diff_key($event,$eventFieldsToDisplay); // perhaps not necessary using this implementation
}

//$log->log($eventFieldsToDisplay);
//$log->log($visibleFields);
	   
foreach($visibleFields as $field) {
	switch($field) {
        case 'event_id':
            $form->addElement('hidden',$field,$field);
            // need to add these as hidden fields - although ministry_id should be a dropdown in some cases
            break;
		case 'ministry_id':
			$ministries=generateMinistryArray($a->getPid());
			//$log->log($ministries);
			if (sizeof($ministries)>1) {
				$form->addElement('select','ministry_id','ministry',$ministries);
			} else {
				$form->addElement('hidden',$field,$field);
				$event['ministry_id']=array_pop($ministries); // can't just call $ministries[0] because I manually assigned numeric keys
			}
			break;
		case 'notes':
			$form->addElement('textarea',$field,$field);
			break;
		case 'begin':
		case 'end':
            $group[] =& HTML_QuickForm::createElement('text', $field.'_date', $field,array('autocomplete'=>'off','id'=>$field.'_date','size'=>10));
            $group[] =& HTML_QuickForm::createElement('text', $field.'_time', $field);
            $form->addGroup($group, $field, $field,'',false);
			unset($group);
        //the order of the date is important for the javascript calendar to work properly - MUST BE d/m/YYYY
            if (!$event[$field]) {
                $event[$field.'_date']=date('n/j/Y',time());
				if ($field=='begin') {
					$event[$field.'_time']='8:00pm';
				} else {
					$event[$field.'_time']='10:00pm';
				}
            } else {
                $event[$field.'_date']=date('n/j/Y',strtotime($event[$field]));
                $event[$field.'_time']=date('g:ia',strtotime($event[$field]));
            }
        //$calendar=generateYahooCalendarJS($field.'_cal',$calNum++,$field.'_date');
                       //$form->addElement('html',$calendar);
		/*
			$calNum=$calNum++; // allows us to generate multiple JS calendars
			$dateFieldName=$field.'_date';
			$element=sprintf('<text id="%s" name="%s" type="text" value="%s"/>',$dateFieldName,$dateFieldName,$event[$field]);
			$extra=generateYahooCalendarJS($field.'Cal',$calNum,$dateFieldName);
	<?php
	//todo - finish adding this js code - see if I can expand it to handle two different calendars on the same page - check Yhaoo YUI docs
	// I still need to register it using YAHOO.example.calendar.cal1.selectEvent.subscribe(handleSelect, YAHOO.example.calendar.cal1, true); 
	?>
</script>
*/
			break;
		case 'baptisms_in_hs':
			$form->addElement('text',$field,'spirit baptisms');
			$form->addRule($field,'must be a number','numeric',null,'client');
			break;
		case 'salvations':
			$form->addElement('text',$field,$field);
			$form->addRule($field,'must be a number','numeric',null,'client');
			break;
		case 'offering':
			$form->addElement('text',$field,$field);
			$form->addRule($field,'must be a dollar amount (no dollar sign)','regex','/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\.[0-9]{1,2})?$/','client');
			break;
		case 'estimated_attendance':
			$form->addElement('text',$field,'estimated attendance');
			$form->addRule($field,'must be a number','numeric',null,'client');
			break;
		case 'event_type':
			$form->addElement('text',$field,'type of event');
			break;
		case 'name':
			$form->addElement('text',$field,'name');
			$form->addRule($field,'you must name your event','required',null,'client');
			break;
		default:
			$form->addElement('text',$field,$field);
		}
}
$group[]=&HTML_QuickForm::createElement('xbutton', 'btnSave', '<img src="graphics/icons/tick.png" height="16" width="16"/> Save', array('class'=>'positive','onclick'=>'this.form.submit()'));
$form->addGroup($group, null, '', ' ');
if (isset($action) && $action=='add') {
	$form->addElement('hidden','action','INSERT');
	$event['salvations']=0;
	$event['baptisms_in_hs']=0;
	$event['offering']=0;
	$event['estimated_attendance']=0;
} else {
	$form->addElement('hidden','action','UPDATE');
}
$form->setDefaults($event);
$form->applyFilter('__ALL__','trim');
$form->getValidationScript();
      //$form->display();
      $renderer =& new HTML_QuickForm_Renderer_Tableless();
      $form->accept($renderer);
	  echo '<div style="clear:both">'.$renderer->toHtml().'</div>';	
      echo '<div id="cal1Container"></div>';
/* @todo deal with this later
foreach ($hiddenFields as $field) {
	switch ($field) {
		default:
			$form->addElement('hidden',$field,$field);
	}
}
*/
	echo '<a href="#">view all possible fields</a></div>';
	?>
	</div>
	<?php
} // end if $event_id===NULL
// display those present -> do this as a javascript replace to allow for dynamic updating
if ($event_id!==NULL) {
    ?>
		<br/>
        <div id="eventattenders" class="subform">
        </div>
        <script type="text/javascript">
		new Ajax.Updater('eventattenders','subforms/event.attendance.php',{parameters: 'event_id=<?php echo $event_id;?>', evalScripts: true});
    //var req = new DataRequestor();
    //req.setObjToReplace('eventattenders');
    //req.addArg(_GET, "event_id", "<?php echo $event_id;?>");
    //req.getURL('subforms/event.attendance.php');
    </script>
        <?php
} // end $event_id !==NULL
?>
</div>
<?php include 'templates/footer.php';?>
</body>
</html>