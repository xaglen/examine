<?php
/**
 * config.php provides global variable $dsn
 * funky require code necessary because PHP evals relative paths according
 * to location of script execution, not according to location of file
 * No. Really.
 */
require_once dirname(__FILE__).'/../config.php';

/**
 * Simplifies db access. Will be called on almost every user page.
 *
 * <code>
 * <?php
 * $db=createDB(); // will create an MDB2 object with Extended module loaded
 * ?>
 * </code>
 * @global $dsn from config.php
 * @return returns a new MDB2 database object
 */
function createDB() {
    global $dsn;

    $db =& MDB2::factory($dsn);
    $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
    $db->loadModule('Extended');
    $db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);
    return $db;
}

/*
 * Returns a person's full name given their people_id
 *
 * @param int $people_id primary key for people table
 * @return string full name of person
 */
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

/**
 * Returns an array of names in a ministry (useful for dropdowns and lookups)
 *
 * @param int $ministry_id primary key for table ministries
 * @return array an array of names
 */
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

/**
 * Returns the first name of a person given their people_id
 *
 * @param int $people_id primary key to table people
 * @return string first name
 */
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

/**
 * Returns the last name of a person given their people_id
 *
 * @param int $people_id primary key to table people
 * @return string last name
 */
function getLastName($people_id=NULL) {
	$db=createDB();
	$sql='select last_name FROM people WHERE people_id='.$people_id;
	$last_name=$db->getOne($sql);
	return $last_name;
}

/**
 * Returns the name of a subgroup (Bible study, worship team, etc)
 *
 * @param int$subgroup_id primary key to table subgroups
 * @return string 
 */
function getSubgroupName($subgroup_id=NULL) {
	if ($subgroup_id===NULL) return '';
	$db=createDB();
	$sql="select name FROM subgroups WHERE subgroup_id=$subgroup_id";
	$name=$db->getOne($sql);
	return $name;
}

/**
 * Was a student present at an event or not?
 *
 * @param int $people_id primary key to table people
 * @param int @event_id primary key to table events
 * @return boolean
 */
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

/**
 * Returns a count of those who attended an event
 *
 * @param int $event_id primary to key to table events
 * @return int 
 */
function getEventAttendance($event_id=NULL) {
	if ($event_id===NULL) return 0;
	
	$db=createDB();
	$sql='select COUNT(*) FROM event_attendance WHERE event_id='.$event_id;
	$count=$db->getOne($sql);
	return $count;
}

/**
 * Modifies an email address to make it less spam-scraper-friendly
 *
 * @param string $email a standard email address
 * @return string a complete mailto link
 */
function obscureEmail($email) {
	$partA = substr($email, 0, strpos($email, '@'));
	$partB = substr($email, strpos($email, '@'));
	$partB = rtrim($partB);
	$linkText = (func_num_args() == 2) ? func_get_arg(1) : $email;
	$linkText = str_replace('@', '<span class="obscure">&#64;</span> ', $linkText);
	return '<a href="email" onClick=\'a="'.$partA.'";this.href="ma"+"il"+"to:"+a+"'.$partB.'";\'>'.$linkText.'</a>';
}
?>
