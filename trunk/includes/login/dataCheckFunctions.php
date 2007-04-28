<?php

// examples found at:
// http://codewalkers.com/tutorials.php?show=47
// http://etext.lib.virginia.edu/helpsheets/regex.html

/*****
 * isDigits()
 * checks that the input is composed entirely of digits (or ".")
 *****/
function isDigits($element) {
	return !preg_match ("/[^0-9.]/", $element) ;
}


/*****
 * onlyDigits()
 * checks that the input is composed only of digits
 *****/
function onlyDigits($element) {
	return !preg_match ("/[^0-9]/", $element);
}


/*****
 * isDate()
 * checks that the input is composed of digits or "-"
 * and is in the form yyyy-mm-dd
 *****/
function isDate($element) {
	$pattern="/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/";
	return preg_match ($pattern, $element);
}

/*****
 * isTime()
 * checks that the input is composed of digits or ":"
 * and is in the form hh:mm, or in the form hh:mm:ss
 *****/
function isTime($element) {
	$pattern="/^[0-2][0-9]" . "(:[0-5][0-9]){1,2}$/";
	return preg_match ($pattern, $element);
}

/*****
 * isOffering()
 * checks that the input is composed of digits or "."
 * and is in the form ddddddd.cc or ddddddd (or less d digits...)
 *****/
function isOffering($element) {
	$pattern="/^[0-9]{1,7}(\.[0-9][0-9]){0,1}$/";
	return preg_match ($pattern, $element);
}

/*****
 * isLetters()
 * checks that the input is composed entirely of letters (or "-", "_" or ".")
 *****/
function isLetters($element) {
	return !preg_match ("/[^A-z-_. ]/", $element);
}


/*****
 * onlyLetters()
 * checks that the input is composed only of letters
 *****/
function onlyLetters($element) {
	return !preg_match ("/[^A-z]/", $element);
}


/****
 * isAlphaNumeric()
 * checks that the input is composed of letters AND/OR numbers (or "-", "_" or ".")
 *****/
function isAlphaNumeric($element) {
	return !preg_match ("/[^A-z0-9-_.]/", $element);
}


/****
 * isAlphaNumericString()
 * checks that the input is composed of letters AND/OR numbers (or "-", "_", "." or "#")
 *****/
function isAlphaNumericString($element) {
	return !preg_match ("/[^A-z0-9-_.# ]/", $element);
}


/****
 * isOKtext()
 * checks that the input is composed of letters AND/OR numbers (or the other listed characters)
 *****/
function isOKtext($element) {
	return !preg_match ("/[^A-z0-9-_,.@#$%&*=+:;<>?)('\" ]/", $element);
}


/****
 * onlyAlphaNumeric()
 * checks that the input is composed only of letters AND/OR numbers
 *****/
function onlyAlphaNumeric($element) {
	return !preg_match ("/[^A-z0-9]/", $element);
}


/****
 * safeQuery()
 * checks that the input is composed only of letters AND/OR numbers (or the other listed characters)
 *****/
function safeQuery($element) {
	return !preg_match ("/[^A-z0-9.')(*#,<>=\- ]/", $element);
}


/*****
 * checkLength()
 * checks that the length of the input is within set limits
 *****/
function checkLength($string, $min, $max) {
	$length = strlen ($string);
	if (($length < $min) || ($length > $max)) {
		return FALSE;
	} else {
		return TRUE;
	}
}


/*****
 * checkZipCode()
 * checks that the Zip code is valid
 * (checks for U.S., Mexico and Canada, and can be expanded)
 *****/
function checkZipCode($code, $country) {
	$code = preg_replace("/[\s|-]/", "", $code);
	$length = strlen ($code);

	switch ( strtoupper ($country) ) {
		case 'US':
		case 'MX':
			if ( ($length <> 5) && ($length <> 9) ) {
				return FALSE;
			}
			return isDigits($code);
		case 'CA':
			if ($length <> 6) {
				return FALSE;
			}
			return preg_match ("/([A-z][0-9]){3}/", $code);
	}
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
 * checkPhone()
 * checks that the input is structured like a valid phone number
 *****/
function checkPhone($element) {
	$pattern="/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/";
	return preg_match ($pattern, $element);
}


/*****
 * checkPassword()
 * checks that the submitted password is secure enough
 *****/
function checkPassword($password) {
	$length = strlen ($password);

	if ($length < 8) {
		return FALSE;
	}

//	$unique = strlen (count_chars ($password, 3));
//	$difference = $unique / $length;
//	echo $difference;
//
//	if ($difference < .60) {
//		return FALSE;
//	}

//	return preg_match ("/[A-z]+[0-9]+[A-z]+/", $password);
	return !preg_match ("/[^A-z0-9-_,.!@#$%)(&*=+:;<>?~]/", $password);
}


// allow , . ! ? @ # $ % ^ " ' ` ~ & * = ( ) + | [ ] { } \ / < > :
// if I crypt() passwords to save them in the DB, then I shouldn't need this
function handleChars($element) {
	
}

?>
