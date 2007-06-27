<?php
/**
* A collection of functions about users
*
* @package examine
* @subpackage library
*/

class Person {
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
}
?>
