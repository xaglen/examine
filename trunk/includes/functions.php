<?php
require_once 'config.php';

function getName($people_id=NULL) {
	$db=createDB();
	$sql='select preferred_name,first_name,last_name FROM people WHERE people_id='.$people_id;
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'].' '.$row['last_name'];
	} else {
		return $row['preferred_name'].' '.$row['last_name'];
	}
}

function generateNameArray($ministry_id=NULL) {
	$db=createDB();
	$sql='select p.people_id,p.first_name,p.last_name FROM people p,ministry_people mp WHERE mp.ministry_id='.$ministry_id.' AND mp.people_id=p.people_id';
	$result = $db->query($sql);
	//testQueryResult($result,$sql);
	while ($row=$result->fetchRow()) {
		$people[$row['people_id']]=$row['first_name'].' '.$row['last_name'];
	}
	return $people;
}

function getFirstName($people_id=NULL) {
	$db=createDB();
	$sql='select first_name,preferred_name FROM people WHERE people_id='.$people_id;
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'];
	} else {
		return $row['preferred_name'];
	}
}

function getLastName($people_id=NULL) {
	$db=createDB();
	$sql='select last_name FROM people WHERE people_id='.$people_id;
	$last_name=$db->getOne($sql);
	return $last_name;
}

function getSubgroupName($subgroup_id=NULL) {
	if ($subgroup_id===NULL) return '';
	$db=createDB();
	$sql="select name FROM subgroups WHERE subgroup_id=$subgroup_id";
	$name=$db->getOne($sql);
	return $name;
}

function attendedEvent($people_id=NULL,$event_id=NULL) {
	if ($people_id===NULL || $event_id===NULL) return false;
	$db=createDB();
	$sql="select * FROM event_attendance WHERE people_id=$people_id AND event_id=$event_id";
	$attended=$db->getOne($sql);
	if ($attended) {
		return true;
	} else {
		return false;
	}
}

function getEventAttendance($event_id=NULL) {
	if ($event_id===NULL) return 0;
	
	$db=createDB();
	$sql='select COUNT(*) FROM event_attendance WHERE event_id='.$event_id;
	$count=$db->getOne($sql);
	return $count;
}

//
//  obscureEmail()
//  Modifies an email address to make it less spam-scraper-friendly
//  usage: despam($email[, $linkText])
//  returns a complete mailto link
//
function obscureEmail($email) {
	$partA = substr($email, 0, strpos($email, '@'));
	$partB = substr($email, strpos($email, '@'));
	$partB = rtrim($partB);
	$linkText = (func_num_args() == 2) ? func_get_arg(1) : $email;
	$linkText = str_replace('@', '<span class="obscure">&#64;</span> ', $linkText);
	return '<a href="email" onClick=\'a="'.$partA.'";this.href="ma"+"il"+"to:"+a+"'.$partB.'";\'>'.$linkText.'</a>';
}
?>