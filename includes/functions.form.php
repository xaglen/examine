<?php
/**
 * This file contains functions useful for building forms.
 * @package examine
 * @subpackage library
 * @author Glen Davis and Brian Kloefkorn
 */

/**
 * config.php provides global variable $dsn
 * funky require code necessary because PHP evals relative paths according
 * to location of script execution, not according to location of file.
 * No. Really.
 */
require_once dirname(__FILE__).'/../config.php';

require_once dirname(__FILE__).'/functions.php';

/**
 * Creates a dropdown of all the people in a ministry
 *
 * @param int $ministry_id primary key to table ministries
 * @param string $name desired name of the dropdown
 * @param int $default the pid of the person who is to be the default value in the dropdown
 * @return string 
 * @author Glen Davis
 */
function generatePeopleDropdown($ministry_id=NULL,$name='pid',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select p.pid,p.first_name,p.last_name FROM people p,ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.pid=p.pid ORDER BY p.last_name,p.first_name";
	$result=$db->query($sql);
	//testQueryResult($result,$sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['pid']) {
			$dropdown.='<option value='.$row['pid'].' selected>'.$row['first_name'].' '.$row['last_name']."\n";
		} else {
			$dropdown.='<option value='.$row['pid'].'>'.$row['first_name'].' '.$row['last_name']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

/**
 * Creates a dropdown of all the schools associated with a ministry
 *
 * @param int $ministry_id primary key of table ministries
 * @param string $name desired name of the dropdown
 * @param int $default the school_id of the school that is to be the default value in the dropdown
 * @return string 
 * @author Glen Davis
 */
function generateSchoolDropdown($ministry_id=NULL,$name='school_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select s.school_id,s.name FROM schools s, people p, ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.pid=p.pid AND p.school_id=s.school_id ORDER BY s.name";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['SchoolID']) {
			$dropdown.='<option value='.$row['school_id'].' selected>'.$row['school_name']."\n";
		} else {
			$dropdown.='<option value='.$row['school_id'].'>'.$row['school_name']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

/**
 * Creates a dropdown of all the categories a person can be in
 *
  * @param string $name desired name of the dropdown
 * @param int $default the category_id of the category that is to be the default value in the dropdown
 * @return string 
 * @author Glen Davis
 */
function generateCategoryDropdown($name='category_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select category_id,category FROM categories ORDER BY category";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['category_id']) {
			$dropdown.='<option value='.$row['category_id'].' selected>'.$row['category']."\n";
		} else {
			$dropdown.='<option value='.$row['category_id'].'>'.$row['category']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

/**
 * Creates a dropdown of all the subgroups (Bible studies and whatnot) associated with a ministry
 *
 * @param int $ministry_id primary key of table ministries
 * @param string $name desired name of the dropdown
 * @param int $default the subgroup_id of the school that is to be the default value in the dropdown
 * @return string 
 * @author Glen Davis
 */
function generateSubgroupDropdown($ministry_id=NULL,$name='subgroup_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select s.subgroup_id,s.time,s.location FROM subgroups s, ministry_people mp, subgroup_people sp WHERE mp.ministry_id=$ministry_id AND sp.pid=m.pid AND s.subgroup_id=sp.subgroup_id";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['subgroup_id']) {
			$dropdown.='<option value='.$row['subgroup_id'].' selected>'.getSubgroupName($row['subgroup_id'])."\n";
		} else {
			$dropdown.='<option value='.$row['subgroup_id'].'>'.getSubgroupName($row['subgroup_id'])."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

/**
 * creates the javascript necessary for an Ajax textbox
 *
 * @param int $ministry_id primary key to table ministries
 * @return string
 * @author Glen Davis
 */
function generatePeopleLivesearch($ministry_id=NULL) {
	$livesearch="<script>var peoplearray=new Array(";
	$people=generateNameArray($ministry_id);
	foreach($people as $person) {
		$livesearch.="'".addslashes($person)."',";
	}
	$livesearch=rtrim($livesearch,',');
	$livesearch.=');</script>';
	$livesearch.="\n";
	$livesearch.='<form name="peoplesearch" action="people.php"><input type="text" name="person" value ="" onfocus="actb(this,event,peoplearray);" autocomplete="off"><INPUT type="submit" name="create" value="assign"></form>';
	//              $livesearch.='<div align="left" class="box" id="autocomplete" style="WIDTH:100px;BACKGROUND-COLOR:#ccccff"></div>';
	//$livesearch.="\n";
	return $livesearch;
}

/**
  * Outputs a drop-down list of states
 * @param string $name the name of the input field
 * @param string $state a value to mark as "selected" (usually a value in the $_POST variable)
 * @return void
 * @author Brian Kloefkorn
 */
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


/**
  * Returns an array with save/display lists for states (primarily intended for html select drop-down lists)
  *
  * @return array
  * @author Brian Kloefkorn
  */
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


/**
 * Outputs a drop-down list of hours
 *
 * @param string $name the name of the input field
 * @param string $hour a value to mark as "selected" (usually a value in the $_POST variable)
 * @author Brian Kloefkorn
 */
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

/**
 * Outputs a drop-down list of minutes
 *
 * @param string $name the name of the input field
 * @param string $minute a value to mark as "selected" (usually a value in the $_POST variable)
 * @author Brian Kloefkorn
 **/
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


/**
 * Outputs a drop-down list of months
 * 
 * @param string $name - the name of the input field
 * @param string $month (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 * @return void
 * @author Brian Kloefkorn
 */
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


/**
 * Outputs a drop-down list of days of the week
 *
 * @param string $name - the name of the input field
 * @param string $day (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 * @author Brian Kloefkorn
 */
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

/**
 * Calls makeDropDownHTML() with correct values to output an html select list of name titles
 * 
 * @param string $name - the name of the input field
 * @param string $default - text for first item on select list (doesn't have a value)
 * @param string $selectedValue (optional) - a value to mark as "selected" (usually a value in the $_POST variable)
 * @author Brian Kloefkorn
 */
function titleList ($name, $default, $selectedValue="") {
	$titles = array('Mr.','Mrs.','Miss','Ms.','Rev.','Dr.');
	makeDropDownHTML($name, $default, $titles, $titles, $selectedValue);
}

/**
 * formats a phone number for consistent entry into database
 *
 * @param string $element a phone number
 * @return string
 * @author Brian Kloefkorn
 */
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


/**
 * Takes date in the form MM/DD/YYYY or MM-DD-YYYY and re-formats it for insertion into MySQL (YYYY-MM-DD)
 * 
 * @param string $strDate a date in standard American format
 * @return string
 * @author Brian Kloefkorn
 */
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

/**
 * Returns the <div> and javascript necessary to invoke a YUI calendar widget.
 * Will not work unless the appropriate js libraries have been loaded. See 
 * http://developer.yahoo.com/yui/calendar/
 *
 * @param string $divLabel the name for the div into which the calendar will be written
 * @param int $calNum the number of the calendar being generated - allows for multiple calendars on one page
 * @param string $linkedField the <INPUT> field to be updated when a calendar selection is made
 * @return string an HTML snippet containing a div and javascript
 */
function generateYahooCalendarJS($divLabel='cal',$calNum=1,$linkedField='date') {
	$calendar="\n<div id='$divLabel'></div>\n";
	$calendar.='<script type="text/javascript">';
	$calendar.='function handleCalendar'.$calNum.'Select(type,args,obj) {';
	$calendar.='var dates = args[0]; var date = dates[0]; var year = date[0], month = date[1], day = date[2];';
	$calendar.='var txtDate = document.getElementById("'.$linkedField.'");';
	$calendar.='txtDate.value = month + "/" + day + "/" + year;';
	$calendar.='}';
	$calendar.='YAHOO.namespace("example.calendar");';
	$calendar.='function init() {YAHOO.example.calendar.cal'.$calNum.' = new YAHOO.widget.Calendar("cal'.$calNum.'","'.$divLabel.'"); YAHOO.example.calendar.cal'.$calNum.'.render(); } ';
	$calendar.='YAHOO.util.Event.addListener(window, "load", init);';
	$calendar.='YAHOO.example.calendar.cal'.$calNum.'.selectEvent.subscribe(handleCalendar'.$calNum.'Select, YAHOO.example.calendar.cal'.$calNum.', true);';
	$calendar.='</script>';
	return $calendar;
}
?>
