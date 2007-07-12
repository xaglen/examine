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
 * Returns an array of ministries connected to a user (useful for dropdowns and lookups)
 * Does not check for permissions to edit ministries - it just returns a list.
 *
 * @param int $pid primary key for table people
 * @return array an array of names
 */
function generateMinistryArray($pid=NULL) {
	$db=createDB();
	$sql='select m.ministry_id,m.name FROM ministries m,ministry_people mp WHERE mp.pid='.$pid.' AND m.ministry_id=mp.ministry_id';
	$result = $db->query($sql);
	//testQueryResult($result,$sql);
	while ($row=$result->fetchRow()) {
		$ministries[$row['ministry_id']]=$row['name'];
	}
	return $ministries;
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
 * Sets a systemwide option
 *
 * @param string $configname the option to be set
 * @param string $configval what the preference is - a serialized PHP variable. It is the responsibility of the calling function to serialize the data.
 */
function setSystemVariable($configname=null, $configval=null) {
	if ($configname===null || $configval===null) {
		return;
	}
	$db=createDB();
    $configname=$db->quote($configname);
    $configval = $db->quote($configval);
	$sql="INSERT INTO variables (configname, configval) VALUES ($configname,$configval) ON DUPLICATE KEY UPDATE configval=VALUES(configval)";
	$db->exec($sql);
}

/**
 * Retrieves a systemwide setting
 * 
 * @param string $configname n the preference to retrieve
 * @return string a serialized PHP variable. Call unserialize on this returned value.
 */
function getSystemVariable($configname=null) {
	if ($configname===null) {
		return null;
	}
	
	$db=createDB();
	$configname=$db->quote($configname);
	$sql="SELECT configval FROM variables WHERE configname=$configname";
	$configval=$db->getOne($sql); // option is the key, so there will never be two entries
	return $configval;
}
?>
