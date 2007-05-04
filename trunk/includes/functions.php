<?php
/**
 * this file contains the core functions almost every script will require
 *
 * @package examine
 * @subpackage library
 * @author Glen Davis
 */

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
 * Returns a person's full name given their pid
 *
 * @param int $pid primary key for people table
 * @return string full name of person
 */
function getName($pid=NULL) {
	$db=createDB();
	$sql='select preferred_name,first_name,last_name FROM people WHERE pid='.$pid;
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
	$sql='select p.pid,p.first_name,p.last_name FROM people p,ministry_people mp WHERE mp.ministry_id='.$ministry_id.' AND mp.pid=p.pid';
	$result = $db->query($sql);
	//testQueryResult($result,$sql);
	while ($row=$result->fetchRow()) {
		$people[$row['pid']]=$row['first_name'].' '.$row['last_name'];
	}
	return $people;
}

/**
 * Returns the first name of a person given their pid
 *
 * @param int $pid primary key to table people
 * @return string first name
 */
function getFirstName($pid=NULL) {
	$db=createDB();
	$sql='select first_name,preferred_name FROM people WHERE pid='.$pid;
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'];
	} else {
		return $row['preferred_name'];
	}
}

/**
 * Returns the last name of a person given their pid
 *
 * @param int $pid primary key to table people
 * @return string last name
 */
function getLastName($pid=NULL) {
	$db=createDB();
	$sql='select last_name FROM people WHERE pid='.$pid;
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
 * @param int $pid primary key to table people
 * @param int @event_id primary key to table events
 * @return boolean
 */
function attendedEvent($pid=NULL,$event_id=NULL) {
	if ($pid===NULL || $event_id===NULL) return false;
	$db=createDB();
	$sql="select * FROM event_attendance WHERE pid=$pid AND event_id=$event_id";
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

/**
 * Sets a user preference
 *
 * @param int $pid primary key for table people
 * @param string $option the preference to be set
 * @param string $value what the preference is - a serialized PHP variable. It is the responsibility of the calling function to serialize the data.
 */
function setUserPreference($pid=null,$option=null,$value=null) {
	if ($pid===null || $option===null || $value===null) {
		return;
	}
	$db=createDB();
	$pid=$db->quote($pid);
	$option=$db->quote($option);
	$value=$db->quote($value);
	$sql="INSERT INTO user_preferences (pid,option,value) VALUES ($pid,$option,$value) ON DUPLICATE KEY UPDATE value=VALUES(value)";
	$db->exec($sql);
}

/**
 * Retrieves a user preference
 * 
 * @param int $pid primary key for table people
 * @param string $option the preference to retrieve
 * @return string $value a serialized PHP variable. Call unserialize on this returned value.
 */
function getUserPreference($pid=null,$option=null) {
if ($pid===null || $option===null) {
		return null;
	}
	
	$db=createDB();
	$option=$db->quote($option);
	$sql="SELECT value FROM user_preferences WHERE pid=$pid and option='$option'";
	$value=$db->getOne($sql); // pid and option are the key together, so there will never be two entries
	return $value;
}

/**
 * Sets a systemwide option
 *
 * @param string $option the option to be set
 * @param string $value what the preference is - a serialized PHP variable. It is the responsibility of the calling function to serialize the data.
 */
function setSystemVariable($option=null, $value=null) {
	if ($option===null || $value===null) {
		return;
	}
	$db=createDB();
    $option=$db->quote($option);
    $value = $db->quote($value);
	$sql="INSERT INTO variables (option,value) VALUES ($option,$value) ON DUPLICATE KEY UPDATE value=VALUES(value)";
	$db->exec($sql);
}

/**
 * Retrieves a systemwide setting
 * 
 * @param string $option the preference to retrieve
 * @return string $value a serialized PHP variable. Call unserialize on this returned value.
 */
function getSystemVariable($option) {
	if ($option===null) {
		return null;
	}
	
	$db=createDB();
	$option=$db->quote($option);
	$sql="SELECT value FROM variables WHERE option='$option'";
	$value=$db->getOne($sql); // option is the key, so there will never be two entries
	return $value;
}
?>
