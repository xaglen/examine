<?php
/**
* This file should be included at the top of any page needing restricted access
* It both provides the classes necessary for authentication and initializes the
* authentication object.
*
* @package examine
* @subpackage library
*/

class user {
	private $pid=null;
	
	function __construct($pid=null) {
		if (ctype_digit($pid)) {
			$this->pid=$pid;
		}		
	}
	
	function getUserId() {
		return $this->pid;
	}

/**
 * Returns the first name of a person
 *
 * @return string first name
 */
function getFirstName() {
	$db=createDB();
	$sql='select first_name,preferred_name FROM people WHERE pid='.$this->getUserId();
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
function getLastName() {
	$db=createDB();
	$sql='select last_name FROM people WHERE pid='.$this->getUserId();
	$last_name=$db->getOne($sql);
	return $last_name;
}

/*
 * Returns a person's full name given their pid
 *
 * @param int $pid primary key for people table
 * @return string full name of person
 */
function getName() {
	$db=createDB();
	$sql='select preferred_name,first_name,last_name FROM people WHERE pid='.$this->getUserId();
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'].' '.$row['last_name'];
	} else {
		return $row['preferred_name'].' '.$row['last_name'];
	}
}

/**
 * Sets a user preference
 *
 * @param string $prefname the preference to be set
 * @param string $prefval what the preference is - a serialized PHP variable. It is the responsibility of the calling function to serialize the data.
 */
function setUserPreference($prefname=null,$prefval=null) {
	if ($prefname===null || $prefval===null) {
		return;
	}
	$db=createDB();
	$user_id=$this->getUserId();
	$prefname=$db->quote($prefname);
	$prefval=$db->quote($prefval);
	$sql="INSERT INTO user_preferences (user_id,prefname,prefval) VALUES ($user_id,$prefname,$prefval) ON DUPLICATE KEY UPDATE prefval=VALUES(prefval)";
	$db->exec($sql);
}

/**
 * Retrieves a user preference
 * 
 * @param string $prefname the preference to retrieve
 * @return string a serialized PHP variable. Call unserialize on this returned value.
 */
function getUserPreference($prefname=null) {
if ($prefname===null) {
		return null;
	}
	
	$db=createDB();
	$prefname=$db->quote($prefname);
	$user_id=$this->getUserId();
	$sql="SELECT prefval FROM user_preferences WHERE user_id=$user_id AND prefname=$prefname";
	$prefval=$db->getOne($sql); //user_pid and prefname are the key together, so there will never be two entries
	return $prefval;
}

}
?>
