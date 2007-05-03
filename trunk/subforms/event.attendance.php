<?php
require_once basedir(__FILE__).'/../includes/functions.php';

$db=createDB();

if (!isset($_REQUEST['event_id'])) {
	exit();	
} 

$event_id=$_REQUEST['event_id'];

if (!isset($_REQUEST['ministry_id'])) {
    exit();
}

$ministry_id=$_REQUEST['ministry_id'];

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
?>
<FORM name="present" TYPE="POST" ACTION="<?php echo $_SERVER['PHP_SELF'];?>">
<?php echo generatePeopleDropDown($ministry_id,'pid');?>
<input type="button" name="add" value="add this student" onclick="addItem('present','pid','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'pid[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'pid[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'pid[]');">invert</a><br/>
<input type="hidden" name="event_id" value="<?php echo $event_id;?>"/>
<?php
$sql="SELECT first_name,last_name,s.pid FROM event_attendance e,students s WHERE e.pid=s.pid AND e.event_id=$event_id ORDER BY last_name,first_name";
$result=$db->query($sql);
//testQueryResult($result);
while ($row=$result->fetchRow()) {
		$Facebook=sprintf('http://facebook.com/search.php?do_search=1&query=%s',urlencode($row['first_name'].' '.$row['last_name']));
	printf('<INPUT TYPE="checkbox" NAME="pid[]" VALUE="%s"><a href="students.php?pid=%s">%s %s</a> (<a href="%s">fb</a>)<br/>',$row['pid'],$row['pid'],$row['first_name'],$row['last_name'],$Facebook);
}
?>
<input type="button" name="remove" value="remove selected" onclick="removeChecked('present','pid[]','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'pid[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'pid[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'pid[]');">invert</a><br/>
</FORM>