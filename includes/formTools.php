<?php
/**
 * this file contains form building functions
 *
 * @package undecided
 * @author Brian Kloefkorn
 */

/********************
 * 
 * Form building functions
 * 
 ********************/

// moved lots of the code to functions.form.php - Glen

/*****
 * makeDropDown()
 * Will generate correct html code for a select drop-down list
 * from data stored in a database
 * Inputs:	name - name of the field in the html form
 * 			DBtable - table where the needed data is stored
 * 			field - name of the field with value to display
 * 			idField - name of the field with value to store
 * 			default - text for first item on select list (doesn't have a value)
 * 			order - SQL for how to order the results
 * 			value - if the form was submitted but needs to be redisplayed (has value in $_POST) 
 *****/
function makeDropDown ( $name, $DBtable, $field, $idField, $default, $order, $value="" ) {
	// query to get needed data
	$query = "SELECT $idField, $field FROM $DBtable";
	if ( $order ) $query .= " ".$order;
	// get the information
	$result = returnQuery($query);
	if ( $result === FALSE ) {
		echo '<p class="error">Error: could not access data for ' . $name . '.</p>'."\n";
	} else {	// make drop-down
		echo '<select name="' . $name . '">' . "\n";
		echo "\t".'<option value="">' . $default . '</option>'."\n";
		while ( $row = $result->fetchRow() ) {
			if ( $value == $row[0] ) $selected = ' selected="selected"'; else $selected = '';
			echo "\t" . '<option value="' . $row[0] . '"' . $selected . '>' . $row[1] . '</option>'."\n";
		}
		echo '</select>'."\n\n";
	}
}


/*****
 * makeDropDownHTML()
 * Outputs html for a select drop-down list from the received data
 * receives: name - the name of the input field
 * 			 default - text for first item on select list (doesn't have a value)
 * 			 display - array of values to display
 * 			 value - array of values to submit
 * 			 selectedValue (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function makeDropDownHTML ( $name, $default, $display, $value, $selectedValue="" ) {
	echo '<select name="' . $name . '">'."\n";
	echo "\t".'<option value="">' . $default . '</option>'."\n";
	for ($i=0; $i<count($value); $i++) {
		if ( $selectedValue == $value[$i] ) $selected = ' selected="selected"'; else $selected = '';
		echo "\t" . '<option value="' . $value[$i] . '"' . $selected . '>' . $display[$i] . '</option>'."\n";
	}
	echo '</select>'."\n\n";
}






/********************
 * 
 * Data check functions
 * MOST OF THESE ARE COVERED IN A PEAR class called Validate
 * 
 ********************/


/*****
 * onlyDigits()
 * checks that the input is composed only of digits
 *****/
function onlyDigits($element) {
	return !preg_match ("/[^0-9]/", $element);
}


/*****
 * onlyLetters()
 * checks that the input is composed only of letters
 *****/
function onlyLetters($element) {
	return !preg_match ("/[^A-z]/", $element);
}


/*****
 * isLetters()
 * checks that the input is composed entirely of letters (or "-", "_" or ".")
 *****/
function isLetters($element) {
	return !preg_match ("/[^A-z-_. ]/", $element);
}


/****
 * isAlphaNumeric()
 * checks that the input is composed of letters AND/OR numbers (or "-", "_" or ".")
 *****/
function isAlphaNumeric($element) {
	return !preg_match ("/[^A-z0-9-_.]/", $element);
}


/*****
 * validZip()
 * checks that the input is composed only of digits or a dash
 *****/
function validZip($element) {
	return !preg_match ("/[^0-9-]/", $element);
}


/*****
 * looseText()
 * checks the input against a list of allowed characters
 *****/
function looseText($element) {
	return !preg_match ("/[^A-z0-9-_,.@#$%&~!:+<>?\)\('\]\[\/\" ]/", $element);
}


/*****
 * validText()
 * checks the input against a list of allowed characters 
 *****/
function validText($element) {
	return !preg_match ("/[^A-z0-9-,.&$#'?!\/)(\" ]/", $element);
}


/*****
 * tightText()
 * checks the input against a list of allowed characters 
 *****/
function tightText($element) {
	return !preg_match ("/[^A-z-,. ]/", $element);
}


/*****
 * validURL()
 * checks the input against a list of allowed characters for URLs
 *****/
function validURL($element) {
	return !preg_match ("/[^A-z0-9-/.?&=:%~]/", $element);
}


/*****
 * checkEmail()
 * checks that the input is structured like a valid email address
 *****/
function checkEmail($element) {
	$pattern = "/^[A-z0-9\._-]+"
		   . "@"
		   . "[A-z0-9][A-z0-9-]*"
		   . "(\.[A-z0-9_-]+)*"
		   . "\.([A-z]{2,6})$/";
	return preg_match ($pattern, $element);
}




/***************
 * errorMessages()
 * Displays error messages for a section of the form
 * Input:  accepts a variable-length list of arguments
 ***************/
function errorMessages() {
	$numArgs = func_num_args();	// gets the number of arguments passed to the function
	if ( $numArgs > 1 ) {	// more than one argument
		$messages = func_get_args();
		$hasError = false;
		// check to see if any of the arguments did contain an error message
		foreach ( $messages as $value ) if ( $value ) $hasError = true;
		// if there was at least one error message to display, 
		// output a new row with all of the error messages
		if ( $hasError ) {
			for ($i = 0; $i < $numArgs; $i++) {
				if ( $messages[$i] ) echo $messages[$i].'<br />'."\n";	// output a line if there is an error message
			}
		}
	} elseif ( $numArgs == 1 ) {	// only one argument
		$message = func_get_arg(0);
		// if the argument contained an error message, output a row to display it
		if ( $message ) {
			echo $message;
		}
	}
	
	// for 0 arguments, function does nothing
}



/********************
 * 
 * Navigation functions
 * 
 ********************/

/*****
 * pageNumberNav()
 * Displays navigation links to multiple pages of results
 *****/
function pageNumberNav ( $totalPages, $currPage, $url='' ) {
	$content = '<div class="pageNav">'."\n";

	// display link for FIRST page
	if ( $currPage > 1 ) {
		$link = $currPage - 1;
		$content .= '<a href="?'.$url.'page='.$link.'" class="prev">'
			. '&lt; prev</a>'."\n";
	} else {
		$content .= '<span class="prev">&lt; prev</span>'."\n";
	}

	// display current page number and 
	for ( $i=$currPage-2; $i<=$currPage+2; $i++ ) {
		if ( $i > 0 && $i<=$totalPages ) {
			if ( $currPage == $i ) {
				$content .= '<span class="thisPage">'.$i.'</span>'."\n";
			} else {
				$content .= '<a href="?'.$url.'page='.$i.'">'.$i.'</a>'."\n";
			}
		}
	}

	// display link for LAST page
	if ( $currPage < $totalPages ) {
		$link = $currPage + 1;
		$content .= '<a href="?'.$url.'page='.$link.'" class="next">'
			. 'next &gt;</a>'."\n";
	} else {
		$content .= '<span class="next">next &gt;</span>'."\n";
	}

	$content .= '</div>'."\n\n";

	return $content;
}
?>
