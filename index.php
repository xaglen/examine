<?php 
/**
 * this file is the main screen a user sees
 *
 * @package examine
 * @subpackage interface
 */
require_once 'config.php';
require_once 'includes/authentication_header.php';
require_once 'includes/functions.php';

$db=createDB();
$ministry_id=1; // testing @todo create API to get this
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>eXAmine: XA adMINistration</title>
<link rel="stylesheet" href="examine.css" type="text/css">
<link rel="stylesheet" href="css/tabs.css" type="text/css">
<script type="text/javascript" src="forms.js"></script>
<script type="text/javascript" src="display.js"></script>
</head>
<body>
<?php include 'templates/header.php';?>
<div id="main">
<div id="sidebar">
Birthdays This Month & Next: <br/>
<ol>
<?php
$sql="SELECT p.pid,p.birthdate,UNIX_TIMESTAMP(p.birthdate) as unixdate, (YEAR(CURRENT_DATE())-YEAR(p.birthdate)) as age FROM people p,ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.pid=p.pid AND (MONTH(p.birthdate)=MONTH(CURRENT_DATE()) OR MONTH(p.birthdate)=MONTH(DATE_ADD(CURRENT_DATE(),INTERVAL 1 MONTH))) ORDER BY MONTH(p.birthdate),DAYOFMONTH(p.birthdate)";
$result=$db->query($sql);
while ($row=$result->fetchRow()) {
	echo '<li><a href="people.php?id='.$row['pid'].'">'.getName($row['pid']).'</a>: born '.date('F jS, Y',$row['unixday']).', turning '.$row['age'].'.</li>';
}
?>
</ol>

</div>
<h1>eXAmine</h1>

Total Students In Database:
<?php
$sql="SELECT COUNT(*) FROM people p, ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.pid=p.pid AND (p.category_id=1 OR p.category_id=2)";
$count=$db->getOne($sql);
echo $count;

$sql="SELECT COUNT(DISTINCT p.pid) FROM people p, ministry_people mp, email_addresses ea WHERE mp.ministry_id=$ministry_id AND mp.pid=p.pid AND (p.category_id=1 OR p.category_id=2) AND ea.pid=p.pid AND ea.receive_emails=1";
$count=$db->getOne($sql);
echo  " ($count receive email)";
?>
<br/>

How Many Have Shown Up:
<?php
$sql="SELECT COUNT(*) FROM event_attendance ea,people p, ministry_people mp WHERE mp.pid=p.pid AND ea.pid=p.pid AND (p.category_id=1 OR p.category_id=2)";
$visitors=$db->getOne($sql);
echo $visitors;
?>
<br/>

Have Come 3+ Times:
<?php
$sql="SELECT COUNT(ea.pid) FROM event_attendance ea, ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.pid=ea.pid GROUP BY ea.pid HAVING COUNT(ea.pid)>=3";
$result=$db->query($sql);
$attenders=$result->numRows();
echo $attenders. ' <em><a href="subforms/view.attenders.php?threshold=3">see them</a></em>';
?>
<br/>

7 Most Recent Guests: <br/>
<ol>
<?php
$sql="SELECT DISTINCT p.pid FROM people p,event_attendance ea, ministry_people mp WHERE mp.ministry_id=$ministry_id AND p.pid=ea.pid ORDER BY p.created_on DESC LIMIT 7";
$result=$db->query($sql);
while ($row=$result->fetchRow()) {
    echo '<li><a href="people.php?id='.$row['pid'].'">'.getName($row['pid']).'</a></li>';
}
?>
</ol>
</div>
<?php include 'templates/footer.php';?>
</body>
</html>
