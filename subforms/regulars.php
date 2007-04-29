<?php
if (!isset($db)) {
	$db=createDB();
}
$db2=createDB();
$sql="SELECT people_id,COUNT(people_id) FROM event_attendance GROUP BY people_id HAVING COUNT(people_id)>=3";
$result=$db->query($sql);
$i=0;
while ($row=$result->fetchRow()) {
	$people_id=$row['people_id'];
    $sql="SELECT UNIX_TIMESTAMP(e.begin) FROM events e,event_attendance ea WHERE ea.people_id=$people_id AND e.event_id=ea.event_id ORDER BY e.begin DESC";
	$lastAttended=$db2->getOne($sql);
	$threshold=180*86400;
	//$threshold=56*86400; // 56 days = 8 weeks
	if ((time()-$lastAttended)<$threshold) {
		$students[$i]['name']=getName($people_id);
		$students[$i]['first_name']=getFirstName($people_id);
		$students[$i]['last_name']=getLastName($people_id);
		$students[$i]['attendance']=$row['COUNT(people_id)'];
		$students[$i]['people_id']=$people_id;
		$i++;
	}
}

function cmp($a,$b) {
	if (strcmp($a['last_name'],$b['last_name'])>0) {
		return 1;
	} elseif (strcmp($a['last_name'],$b['last_name'])<0) {
		return -1;
	}
	return strcmp($a['first_name'],$b['first_name']);
}

if (isset($students)) {

usort($students,"cmp");
foreach($students as $student) {
	printf('<INPUT TYPE="checkbox" NAME="people_id[]" VALUE="%s"><a href="view.student.php?people_id=%s">%s</a> (%s times)<br/>',$student['people_id'],$student['people_id'],$student['Name'],$student['Attendance']);
}
?>
select: <a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'people_id[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'people_id[]');">invert</a><br/>
<?php
}
?>
