<?php
/**
 * provides $dsn global
 * convoluted call necessary because PHP does not compute relative paths from location of file. No - really
 */
require_once dirname(__FILE__).'/../config.php';

/**
 * Takes two timestamps and gives the difference between them in plain English.
 *
 * @param int $start a unix timestamp
 * @param int $end a unix timestamp
 * @return string a short phrase
 */
function readableTimeDiff($start,$end) {
	if ($start<$end) {
		$years    = date('y', $end) - date('y', $start) ;
		$months   = date('m', $end) - date('m', $start) + ($years * 12) ;
		$weeks    = date('W', $end) - date('W', $start) + ($years * 52);
		if ($weeks<=1) {
			$readableTime="last week";
		} elseif ($weeks<=8) {
			$readableTime="$weeks weeks ago";
		} elseif ($months<24) {
			$readableTime="$months months ago";
		} else {
			$readableTime="$years years ago";
		}
	} elseif ($start<$end) {
		$readableTime="beforehand";
	} else {
		$readableTime="simultaneously";
	}
	return $readableTime;
}

/**
 * How old is a person?
 *
 * @param int $people_id primary key to table people
 * @return int the person's age in minutes
 */
function getAge($people_id=NULL) {
	if ($people_id===NULL) return '';
	$db=createDB();
	$sql="SELECT DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birthdate, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birthdate, '00-%m-%d')) AS age FROM people WHERE people_id=$people_id";
	$age=$db->getOne($sql);
	return $age;
}
?>
