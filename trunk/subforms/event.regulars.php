<?php
if (!isset($db)) {
	$db=createDB();
}
$db2=createDB();
$sql="SELECT pid,COUNT(pid) FROM event_attendance GROUP BY pid HAVING COUNT(pid)>=3";
$result=$db->query($sql);
$i=0;
while ($row=$result->fetchRow()) {
	$pid=$row['pid'];
    $sql="SELECT UNIX_TIMESTAMP(e.begin) FROM events e,event_attendance ea WHERE ea.pid=$pid AND e.event_id=ea.event_id ORDER BY e.begin DESC";
	$lastAttended=$db2->getOne($sql);
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
	printf('<INPUT TYPE="checkbox" NAME="pid[]" VALUE="%s"><a href="view.student.php?pid=%s">%s</a> (%s times)<br/>',$student['pid'],$student['pid'],$student['Name'],$student['Attendance']);
}
?>
select: <a href="#" onclick="setAllCheckBoxes('present', 'pid[]', true);">all</a>
<a href="#" onclick="setAllCheckBoxes('present', 'pid[]', false);">none</a>
<a href="#" onclick="invertAllCheckBoxes('present', 'pid[]');">invert</a><br/>
<?php
}
?>
