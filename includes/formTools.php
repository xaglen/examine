<?php

/********************
 * 
 * Form building functions
 * 
 ********************/


/*****
 * statesList()
 * Outputs a drop-down list of states
 * receives: $name - the name of the input field
 * 			 $state (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function statesList ( $name, $state="" ) {
	$arrays = statesListArrays();
	$value = $arrays[0];
	$display = $arrays[1];
	echo '<select name="'.$name.'">'."\n";
	echo "\t".'<option value="">select a state</option>'."\n";
	for ($i=0; $i<count($value); $i++) {
		if ($value[$i]==$state) $selected = ' selected="selected"'; else $selected = '';
		echo "\t".'<option value="'.$value[$i].'"'.$selected.'>'.$display[$i].'</option>'."\n";
	}
	echo '</select>'."\n";
}


/*****
 * statesListArrays()
 * Returns an array with save/display lists for states (primarily intended for html select drop-down lists)
 *****/
function statesListArrays () {
	$value = array(
		'AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL',
		'GA','HI','ID','IL','IN','IA','KS','KY','LA','ME',
		'MD','MA','MI','MN','MS','MO','MT','NE','NV','NH',
		'NJ','NM','NY','NC','ND','OH','OK','OR','PA','PR',
		'RI','SC','SD','TN','TX','UT','VT','VI','VA','WA',
		'WV','WI','WY');
	$display = array(
		'AL - Alabama','AK - Alaska','AZ - Arizona','AR - Arkansas','CA - California',
		'CO - Colorado','CT - Connecticut','DE - Delaware','DC - District of Columbia','FL - Florida',
		'GA - Georgia','HI - Hawaii','ID - Idaho','IL - Illinois','IN - Indiana',
		'IA - Iowa','KS - Kansas','KY - Kentucky','LA - Louisiana','ME - Maine',
		'MD - Maryland','MA - Massachusetts','MI - Michigan','MN - Minnesota','MS - Mississippi',
		'MO - Missouri','MT - Montana','NE - Nebraska','NV - Nevada','NH - New Hampshire',
		'NJ - New Jersey','NM - New Mexico','NY - New York','NC - North Carolina','ND - North Dakota',
		'OH - Ohio','OK - Oklahoma','OR - Oregon','PA - Pennsylvania','PR - Puerto Rico',
		'RI - Rhode Island','SC - South Carolina','SD - South Dakota','TN - Tennessee','TX - Texas',
		'UT - Utah','VT - Vermont','VI - Virgin Islands','VA - Virginia','WA - Washington',
		'WV - West Virginia','WI - Wisconsin','WY - Wyoming');
	return(array($value, $display));
}


/*****
 * stateName()
 * Returns name of state based on supplied abbreviation
 *****/
function stateName ( $abbr ) {
	switch ( $abbr ) {
		case "AL": return "Alabama";
		case "AK": return "Alaska";
		case "AZ": return "Arizona";
		case "AR": return "Arkansas";
		case "CA": return "California";
		case "CO": return "Colorado";
		case "CT": return "Connecticut";
		case "DE": return "Delaware";
		case "DC": return "District of Columbia";
		case "FL": return "Florida";
		case "GA": return "Georgia";
		case "HI": return "Hawaii";
		case "ID": return "Idaho";
		case "IL": return "Illinois";
		case "IN": return "Indiana";
		case "IA": return "Iowa";
		case "KS": return "Kansas";
		case "KY": return "Kentucky";
		case "LA": return "Louisiana";
		case "ME": return "Maine";
		case "MD": return "Maryland";
		case "MA": return "Massachusetts";
		case "MI": return "Michigan";
		case "MN": return "Minnesota";
		case "MS": return "Mississippi";
		case "MO": return "Missouri";
		case "MT": return "Montana";
		case "NE": return "Nebraska";
		case "NV": return "Nevada";
		case "NH": return "New Hampshire";
		case "NJ": return "New Jersey";
		case "NM": return "New Mexico";
		case "NY": return "New York";
		case "NC": return "North Carolina";
		case "ND": return "North Dakota";
		case "OH": return "Ohio";
		case "OK": return "Oklahoma";
		case "OR": return "Oregon";
		case "PA": return "Pennsylvania";
		case "PR": return "Puerto Rico";
		case "RI": return "Rhode Island";
		case "SC": return "South Carolina";
		case "SD": return "South Dakota";
		case "TN": return "Tennessee";
		case "TX": return "Texas";
		case "UT": return "Utah";
		case "VT": return "Vermont";
		case "VI": return "Virgin Islands";
		case "VA": return "Virginia";
		case "WA": return "Washington";
		case "WV": return "West Virginia";
		case "WI": return "Wisconsin";
		case "WY": return "Wyoming";
		default: return $abbr;
	};
}


/*****
 * hoursList()
 * Outputs a drop-down list of hours
 * receives: $name - the name of the input field
 * 			 $hour (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function hoursList ( $name, $hour="" ) {
	$values = array(1=>'01','02','03','04','05','06','07','08','09','10','11','12');
	echo '<select name="'.$name.'">'."\n";
	echo "\t".'<option value="">--</option>'."\n";
	for ($i=1; $i<count($values)+1; $i++) {
		if ($i==$hour) $selected = ' selected="selected"'; else $selected = '';
		echo "\t".'<option value="'.$values[$i].'"'.$selected.'>'.$values[$i].'</option>'."\n";
	}
	echo '</select>'."\n";
}


/*****
 * minutesList()
 * Outputs a drop-down list of minutes
 * receives: $name - the name of the input field
 * 			 $minute (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function minutesList ( $name, $minute="" ) {
	$values = array('0','1','2','3','4','5','6','7','8','9');
	echo '<select name="'.$name.'">'."\n";
	echo "\t".'<option value="">--</option>'."\n";
	for ($i=0; $i<=59; $i++) {
		if ( array_search($i, $values) !== FALSE ) $i = '0'.$i;	// adds a leading '0' to single-digit values
		if ($i==$minute) $selected = ' selected="selected"'; else $selected = '';
		echo "\t".'<option value="'.$i.'"'.$selected.'>'.$i.'</option>'."\n";
	}
	echo '</select>'."\n";
}


/*****
 * monthsList()
 * Outputs a drop-down list of months
 * receives: $name - the name of the input field
 * 			 $month (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function monthsList ( $name, $month="" ) {
	$value = array('01','02','03','04','05','06','07','08','09','10','11','12');
	$display = array('January','February','March','April','May','June','July','August','September','October','November','December');
	echo '<select name="'.$name.'">'."\n";
	echo "\t".'<option value="">select a month</option>'."\n";
	for ($i=0; $i<count($value); $i++) {
		if ($value[$i]==$month) $selected = ' selected="selected"'; else $selected = '';
		echo "\t".'<option value="'.$value[$i].'"'.$selected.'>'.$display[$i].'</option>'."\n";
	}
	echo '</select>'."\n";
}


/*****
 * daysList()
 * Outputs a drop-down list of days of the week
 * receives: $name - the name of the input field
 * 			 $day (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function daysList ( $name, $day="" ) {
	$value = array('1','2','3','4','5','6','7');
	$display = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	echo '<select name="'.$name.'">'."\n";
	echo "\t".'<option value="">select a day</option>'."\n";
	for ($i=0; $i<count($value); $i++) {
		if ($value[$i]==$day) $selected = ' selected="selected"'; else $selected = '';
		echo "\t".'<option value="'.$value[$i].'"'.$selected.'>'.$display[$i].'</option>'."\n";
	}
	echo '</select>'."\n";
}


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


/*****
 * titleList()
 * Calls makeDropDownHTML() with correct values to output an html select list of name titles
 * receives: name - the name of the input field
 * 			 default - text for first item on select list (doesn't have a value)
 * 			 selectedValue (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 *****/
function titleList ($name, $default, $selectedValue="") {
	$titles = array('Mr.','Mrs.','Miss','Ms.','Rev.','Dr.');
	makeDropDownHTML($name, $default, $titles, $titles, $selectedValue);
}



/********************
 * 
 * Data check functions
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


/*****
 * processPhone()
 * formats a phone number for consistent entry into database
 *****/
function processPhone($element) {
	// strips everything that isn't a digit
	$element = preg_replace("/\D/", "", $element);
	// convert to array of digits
	$d = preg_split("//", $element);
	
	// remove empty cells at beginning and end
	array_shift($d);
	array_pop($d);
	$digits = count($d);
	if ( $digits < 10 ) return (false);	// must have at least 10 digits
	// gives standard U.S. formatting of "123-123-1234"
	$phoneNumber = $d[0].$d[1].$d[2]."-".$d[3].$d[4].$d[5]."-".$d[6].$d[7].$d[8].$d[9];
	// allows for more digits (extensions, etc.) "123-123-1234 1234"
	if ( $digits > 10 ) {
		$phoneNumber .= " ";
		for ( $i=10; $i<$digits; $i++ ) {
			$phoneNumber .= $d[$i];
		}
	}
	return($phoneNumber);
}


/***************
 * formatDate ()
 * Takes date in the form MM/DD/YYYY or MM-DD-YYYY and re-formats it 
 * for insertion into MySQL (YYYY-MM-DD)
 ***************/
function formatDate ( $strDate ) {
	$err = false;
	if ( (strlen($strDate) >= 8) && (strlen($strDate) <= 10) ) {
		$strDate = str_replace('-', '/', $strDate);
		$tempDate = explode('/', $strDate);
		// check for 3 pieces
		if ( count($tempDate) == 3 ) {
			$month = $tempDate[0] + 0;
			$daynum = $tempDate[1] + 0;
			$year = $tempDate[2] + 0;
		} else {
			$err = true;
		}
	} else {
		$err = true;
	}
	// prepend "0" if needed
	$month   = (($month < 10) ? '0'.$month : $month);
	$daynum  = (($daynum < 10) ? '0'.$daynum : $daynum);
	if ( !$err ) {
		return $year. '-' . $month  . '-' . $daynum ;
	} else {
		return $strDate;
	}
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