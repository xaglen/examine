<?php
require_once dirname(__FILE__).'/../includes/functions.php';
require_once dirname(__FILE__).'/../includes/functions.form.php';

function cmp($a,$b) {
	if (strcmp($a['last_name'],$b['last_name'])>0) {
		return 1;
	} elseif (strcmp($a['last_name'],$b['last_name'])<0) {
		return -1;
	}
	return strcmp($a['first_name'],$b['first_name']);
}


$db=createDB();

if (!array_key_exists('event_id',$_REQUEST) || !ctype_digit($_REQUEST['event_id'])) {
	exit();	
} 

$event_id=$_REQUEST['event_id'];
$ministry_id=$db->getOne('SELECT ministry_id FROM events WHERE event_id='.$event_id);

if (isset($_REQUEST['remove'])) {
	if (isset($_REQUEST['pid'])) {
		foreach($_REQUEST['pid'] as $pid) {
			$sql="DELETE FROM event_attendance WHERE event_id=$event_id AND pid=$pid";
			$result=$db->exec($sql);
			//testQueryResult($result);
		}
	}
} elseif (isset($_REQUEST['add'])) {
	if (isset($_REQUEST['pid'])) {
		$pid=$_REQUEST['pid'];
		$sql="INSERT INTO event_attendance (event_id,pid) VALUES ($event_id,$pid)";
		$result=$db->exec($sql);
		//testQueryResult($result);
	}
}

$result=$db->query("SELECT first_name,last_name,p.pid FROM event_attendance e,people p WHERE e.pid=p.pid AND e.event_id=$event_id ORDER BY last_name,first_name");

if ($result->numRows()>0) {
	echo '<h2>Attendance</h2>';
} else {
	echo '<h2>Regulars Who Might Have Been There</h2>';
}

?>
<FORM name="present" TYPE="POST" ACTION="<?php echo $_SERVER['PHP_SELF'];?>">
<?php echo generatePeopleDropDown($ministry_id,'pid');?>
<input type="button" name="add" value="add attender" onclick="addItem('present','pid','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'pid[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'pid[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'pid[]');">invert</a><br/>
<input type="hidden" name="event_id" value="<?php echo $event_id;?>"/>

<?php
if ($result->numRows()>0) {
	while ($row=$result->fetchRow()) {
		$Facebook=sprintf('http://facebook.com/search.php?do_search=1&query=%s',urlencode($row['first_name'].' '.$row['last_name']));
	printf('<INPUT TYPE="checkbox" NAME="pid[]" VALUE="%s"><a href="people.php?pid=%s">%s %s</a> (<a href="%s">fb</a>)<br/>',$row['pid'],$row['pid'],$row['first_name'],$row['last_name'],$Facebook);
	}
} else {
	$sql="SELECT pid,COUNT(pid) FROM event_attendance GROUP BY pid HAVING COUNT(pid)>=3";
	$result=$db->query($sql);
	$i=0;
	while ($row=$result->fetchRow()) {
		$pid=$row['pid'];
		$sql="SELECT UNIX_TIMESTAMP(e.begin) FROM events e,event_attendance ea WHERE ea.pid=$pid AND e.event_id=ea.event_id ORDER BY e.begin DESC";
		$lastAttended=$db->getOne($sql);
		$threshold=180*86400;
	//$threshold=56*86400; // 56 days = 8 weeks
		if ((time()-$lastAttended)<$threshold) {
			$students[$i]['name']=getName($pid);
			$students[$i]['first_name']=getFirstName($pid);
			$students[$i]['last_name']=getLastName($pid);
			$students[$i]['attendance']=$row['COUNT(pid)'];
			$students[$i]['pid']=$pid;
			$i++;
		}
	}

	if (isset($students)) {
		$form->addElement('header','Students Who Might Have Been There','bxy');
		usort($students,"cmp");
		foreach($students as $student) {
			printf('<INPUT TYPE="checkbox" NAME="pid[]" VALUE="%s"><a href="view.student.php?pid=%s">%s</a> (%s times)<br/>',$student['pid'],$student['pid'],$student['Name'],$student['Attendance']);
		}
	}
}
?>
<input type="button" name="remove" value="remove selected" onclick="removeChecked('present','pid[]','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'pid[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'pid[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'pid[]');">invert</a><br/>
</FORM>