<?php
require_once basedir(__FILE__).'/../includes/functions.php';

$db=createDB();

if (!isset($_REQUEST['event_id'])) {
	exit();	
} 

$event_id=$_REQUEST['event_id'];

if (isset($_REQUEST['remove'])) {
	if (isset($_REQUEST['people_id'])) {
		foreach($_REQUEST['people_id'] as $people_id) {
			$sql="DELETE FROM event_attendance WHERE event_id=$event_id AND people_id=$people_id";
			$result=$db->exec($sql);
			//testQueryResult($result);
		}
	}
} elseif (isset($_REQUEST['add'])) {
	if (isset($_REQUEST['people_id'])) {
		$people_id=$_REQUEST['people_id'];
		$sql="INSERT INTO event_attendance (event_id,people_id) VALUES ($event_id,$people_id)";
		$result=$db->exec($sql);
		//testQueryResult($result);
	}
}
?>
<FORM name="present" TYPE="POST" ACTION="<?php echo $_SERVER['PHP_SELF'];?>">
<?php echo generateStudentDropDown('people_id');?>
<input type="button" name="add" value="add this student" onclick="addItem('present','people_id','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'people_id[]');">invert</a><br/>
<input type="hidden" name="event_id" value="<?php echo $event_id;?>"/>
<?php
$sql="SELECT first_name,last_name,s.people_id FROM event_attendance e,students s WHERE e.people_id=s.people_id AND e.event_id=$event_id ORDER BY last_name,first_name";
$result=$db->query($sql);
//testQueryResult($result);
while ($row=$result->fetchRow()) {
		$Facebook=sprintf('http://facebook.com/search.php?do_search=1&query=%s',urlencode($row['first_name'].' '.$row['last_name']));
	printf('<INPUT TYPE="checkbox" NAME="people_id[]" VALUE="%s"><a href="students.php?people_id=%s">%s %s</a> (<a href="%s">fb</a>)<br/>',$row['people_id'],$row['people_id'],$row['first_name'],$row['last_name'],$Facebook);
}
?>
<input type="button" name="remove" value="remove selected" onclick="removeChecked('present','people_id[]','eventattenders',<?php echo '\''.$_SERVER['PHP_SELF'].'\'';?>,'event_id');"/><br/>
select: <a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'people_id[]');">invert</a><br/>
</FORM>
