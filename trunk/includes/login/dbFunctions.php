<?php

// Report all errors except E_NOTICE (uninitialized variables) - php.ini default setting
error_reporting(E_ALL ^ E_NOTICE);

require_once('DB.php');
require_once('dbInfo.php');

// list of common functions for dealing with database
// PEAR


/**************
 * commandQueryQuery()
 * Submits a query to the database that does not need the results 
 *   to be saved (error-free completion indicates that it worked)
 * Inputs:  $query - the query to send to the db
 * Query types:  INSERT, UPDATE, CREATE, DELETE
 **************/
function commandQuery ( $query ) {
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	$res =& $db->query($query);	// run query

	if( $res != DB_OK ) {
//		if ( PEAR::isError($res) ) { // if error querying, abort
		$success = FALSE;
	} else { 
		$success = TRUE;
	}
//		$res->free(); // delete result set and free used memory
//	}
	
	$db->disconnect();			// disconnect from the database
	

	return $success;
}



/**************
 * returnQuery()
 * Submits a query to the database that returns a data set
 * Inputs:  $query - the query to send to the db
 * Query types:  SELECT
 **************/
function returnQuery ( $query ) {
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	$res =& $db->query($query);	// run query
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		return FALSE;
	} else {
		$db->disconnect();			// disconnect from the database
		return $res;
	}
}



/**************
 * getOne()
 * quickly fetches a single piece of information
 * Inputs:  $query - the query to send to the db
 **************/
function getOne ( $query ) {
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	$res = $db->getOne($query);
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		return FALSE;
	} else {
		$db->disconnect();			// disconnect from the database
		return $res;
	}
}



/***************
 * encryptString()
 * Encrypts a string
 * Inputs:	$data - the string to be encrypted
 * Note:  to decrypt a string, the iv must be saved when 
 * 		  the string is first encrypted
 ***************/
function encryptString ( $data, $iv='' ) {
	// Open the cipher
	$td = mcrypt_module_open('rijndael-128', '', 'ecb', '');

	// Create the IV and determine the keysize length
	if ( $iv == '' || $iv == null ) {
		srand();
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	}
	$ks = mcrypt_enc_get_key_size($td);

	// Create key
	$key = substr(md5('chi alphA secrets'), 0, $ks);

	// Intialize encryption
	mcrypt_generic_init($td, $key, $iv);

	// Encrypt data
	$encrypted = mcrypt_generic($td, $data);

	// Terminate encryption handler
	mcrypt_generic_deinit($td);

	return array($encrypted, $iv);
}



/***************
 * decryptString()
 * Decrypts a string
 * Inputs:	$data - the string to be encrypted
 * Note:  to decrypt a string, the iv must be saved when 
 * 		  the string is first encrypted
 ***************/
function decryptString( $data, $iv ) {

	// Open the cipher
	$td = mcrypt_module_open('rijndael-128', '', 'ecb', '');

	// Determine the keysize length
	$ks = mcrypt_enc_get_key_size($td);

	// Create key
	$key = substr(md5('chi alphA secrets'), 0, $ks);

	// Initialize encryption module for decryption
	mcrypt_generic_init($td, $key, $iv);

	// Decrypt encrypted string
	$decrypted = mdecrypt_generic($td, $data);

	// Terminate decryption handle and close module
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);

	// Show string
	return trim($decrypted);
}


/**************
 * showPermissions()
 * Returns an array of the available permissions
 **************/
function showPermissions () {
	$query = "SELECT permissionID, permissionName, permissionDesc FROM permissions";
	$res = returnQuery($query);

	while ( $row =& $res->fetchRow() ) {
		$permissionLevels[] = array($row[0], $row[1], $row[2]);
	}
	$res->free();

	return $permissionLevels;

/*	switch ($clearance) {
	case 1:
	   return ('User can <strong>change page content</strong>, <strong>send email</strong>, and <strong>manage user accounts</strong> (add, remove and modify)');
	case 2:
	   return ('User can <strong>change page content</strong> and <strong>send email</strong>');
	case 3:
	   return ('User can only <strong>change page content</strong>');
	case 12:
	   return ('This user only has access to .htpasswd-protected files');
	}
*/
}



/*************
 * getTables()
 * Gets a list of all the tables in a database and returns the list in an array
 *************/
function getTables() {
	$dsn = unserialize(DSN);

	// connect to database
	$db =& DB::connect($dsn);
	if (PEAR::isError($db)) { die($db->getMessage()); }

	// query
	$query = "show tables";
	
	$res =& $db->query($query);		// run query
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		echo 'Error: could not get data';
		exit;
	}

	// save table names in an array
	$i=0;
	while( $row =& $res->fetchRow() ) {
		$tables[$i] = $row[0];
		$i++;
	}
	
	$res->free();	// delete result set and free used memory
	$db->disconnect(); // disconnect from the database

	return ($tables);
}



/***************
 * tableToCSV()
 * Outputs a csv file (comma-delimited) with all the data from a particular table
 * Inputs:	tableName - name of the table in the database
 * 			outputFile - name of the file you want to be created
 ***************/
function tableToCSV($tableName, $outputFile) {
	$dsn = unserialize(DSN);

	// connect to database
	$db =& DB::connect($dsn);
	if (PEAR::isError($db)) { die($db->getMessage()); }

	// query
	$select = "SELECT * FROM ".$tableName;
	
	$res =& $db->query($select);	// run query
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		echo 'Error: could not get data';
		exit;
	}

	// get the column names
	$info = $db->tableInfo($res);
	for ( $i=0; $i<count($info)-1; $i++ ) {
		$colNames .= $info[$i]['name'] . ',';
	}
	$colNames .= $info[$i]['name'];

	while( $row =& $res->fetchRow() ) {
		$line = '';
		for ($j=0; $j<count($row)-1; $j++) {
			if ( !isset($row[$j]) || $row[$j] == "" ) {
				$value = ",";
			} else {
				$value = $row[$j] . ",";
			}
			$line .= $value;
		}
		// get the last entry, but don't add a comma to the end
		if ( isset($row[$j]) && $row[$j] != "" ) {
			$value = $row[$j];
		}
		$line .= $value;
		$data .= trim($line) . "\n";
	}

	$data = str_replace("\r", "", $data);

	$res->free();	// delete result set and free used memory
	$db->disconnect();	// disconnect from the database

	if ( $data == "" ) {
		$data = "\n(0) Records Found!\n";                        
	}

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".$outputFile);
	header("Pragma: no-cache");
	header("Expires: 0");
	print $colNames."\n".$data;
}



/*************
 * csvToTable()
 *************/
function csvToTable($table, $hasHeadings, $file) {
	$dsn = unserialize(DSN);

	// connect to database
	$db =& DB::connect($dsn);
	if ( PEAR::isError($db) ) { // if error connecting, abort
		die($db->getMessage());
	}

	// get some information about the table
	$select = "SELECT * FROM $table";
	
	$res =& $db->query($select);	// run query
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		echo 'Error: could not get data';
		exit;
	}

	// find the number of columns
	$info = $db->tableInfo($res);
	$numCols = count($info);

	// get the contents of the csv file
	$fcontents = file($file);

	// if the csv file has column headings, read them
	// if not, get them from the database
	if ( $hasHeadings == 'yes' ) {
		$colNames = trim($fcontents[0]);

		$lineStart = 1;
	} elseif ( $hasHeadings == 'no' ) {
		// get the column names and list them
		for ( $i=0; $i<$numCols-1; $i++ ) {
			$colNames .= $info[$i]['name'] . ',';
		}
		$colNames .= $info[$i]['name'];	// no comma after the last item

		$lineStart = 0;
	}

	// list of correct number of placeholders ("?"s) for insert query values
	// see prepare() and execute() functions in PEAR and http://pear.php.net/manual/en/package.database.db.intro-execute.php
	for ( $i=0; $i<$numCols-1; $i++ ) {
		$questions .= '?,';
	}
	$questions .= '?';	// no comma after the last item

	// if there is only one column, strip off the comma	
	if ( $numCols == 1 ) {
		$colNames = rtrim($colNames, ",");
		$questions = rtrim($questions, ",");
	}

	// prepare the query for inserting into this table
	$query = 'INSERT INTO '.$table.' ('.$colNames.') VALUES ('.$questions.')';
	$sth = $db->prepare($query);
	if (PEAR::isError($sth)) {
		die($sth->getMessage());
	}

	// get values from file
	for ($i=$lineStart; $i<count($fcontents); $i++) {
		$line = trim($fcontents[$i]);
		$arr = explode(",", $line);

		$res =& $db->execute($sth, $arr);
		if (PEAR::isError($res)) {
			die($res->getMessage());
		}
	}

//	$res->free();	// delete result set and free used memory
	$db->disconnect(); // disconnect from the database

	return;
}



/***************
 * queryToCSV()
 * Outputs a csv file (comma-delimited) with all the data from a query
 * Inputs:	query - the query to run to get the data
 * 			outputFile - name of the file you want to be created (.csv is appended on the end)
 ***************/
function queryToCSV($query, $outputFile) {
	$dsn = unserialize(DSN);

	// connect to database
	$db =& DB::connect($dsn);
	if (PEAR::isError($db)) { die($db->getMessage()); }

	// run query
	$res =& $db->query($query);
	if ( PEAR::isError($res) ) {	// if error querying, abort
		$res->free();				// delete result set and free used memory
		$db->disconnect();			// disconnect from the database
		echo 'Error: could not get data';
		exit;
	}

	// get the column names
	$info = $db->tableInfo($res);
	for ( $i=0; $i<count($info); $i++ ) {
		$colNames .= '"' . $info[$i]['name'] . '",';
	}
	$colNames = rtrim($colNames, ",");

	// go through the data
	while( $row =& $res->fetchRow() ) {
		$line = '';
		foreach ( $row as $value ) {
			$line .= '"'.$value.'",';
		}
		$line = rtrim($line, ",");
		$data .= $line . "\n";
	}

	$data = str_replace("\r", "", $data);

	$res->free();	// delete result set and free used memory
	$db->disconnect();	// disconnect from the database

	if ( $data == "" ) {
		$data = "\n<p>No records found.</p>\n";                        
	}

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".$outputFile.".csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print $colNames."\n".$data;
}




// old PHP

/*****
 * connectDB()
 * makes a connection with the specified database
 * values are stored in 'dbInfo.php' but are received when the function is called
 *****
function connectDB ($host, $user, $password, $dbase) {
     // connect to MySQL database
     // store link in $connection
     $connection = mysql_connect($host,$user,$password)
          or die("Unable to connect to database.");

     // select the desired database to use
     mysql_select_db($dbase)
          or die("Unable to select database.");

     return ($connection);
}
*/

/*****
 * disconnectDB()
 * ends connection with database
 *****
function disconnectDB ($connection) {
     mysql_close($connection);
}
*/

/**********
 * handleQuery()
 * assembles query, runs it and frees variables
 **********
function handleQuery ( $valueNum, $updateInfo, $pid, $dbHost, $dbUser, $dbPassword, $dbase ) {

		/** assemble query for address info **
		if ( $valueNum ) {
			$query = assembleInsertQuery($updateInfo, $valueNum, $pid);
echo $query."<br>";

			// run query
			runInsert($query, $dbHost, $dbUser, $dbPassword, $dbase);

	        // free variables
			unset($query);
		}

}
*/

/**********
 * runSelect() runs the assembled SELECT query and returns the result
 **********
function runSelect($query, $dbHost, $dbUser, $dbPassword, $dbase) {
	$connection = connectDB($dbHost, $dbUser, $dbPassword, $dbase);

	$result = mysql_query($query)
		or die("couldn't query\n".mysql_error());

	// from the $result, get the rows
	for ( $i=0; $i < mysql_num_rows($result); $i++ ) {
		$resultRowList[$i] = mysql_fetch_array($result);
	}

	disconnectDB($connection);

	mysql_free_result($result);

	return $resultRowList;
}
*/

/**********
 * assembleInsertQuery()
 **********/
function assembleInsertQuery( $tableInsertInfo, $tableValueNum, $pid ) {
	/** handle tableList table values **/

	$query = "INSERT INTO " . $tableInsertInfo[0][0] . " SET ";

	// for new entry, fails first time (set to 0), but then is set for 
	// subsequent email, phone, and address entries
	if ( $pid ) { $query .= "pid='" . $pid . "', "; }

	for ( $i=0; $i<$tableValueNum-1; $i++ ) {
		$query .= $tableInsertInfo[$i][1]. "='" . $tableInsertInfo[$i][2]. "', ";
	}
	$query .= $tableInsertInfo[$i][1]. "='" . $tableInsertInfo[$i][2]. "'\n";

	return $query;
}


/**********
 * runInsert() runs the assembled INSERT query
 **********
function runInsert($query, $dbHost, $dbUser, $dbPassword, $dbase) {
	$connection = connectDB($dbHost, $dbUser, $dbPassword, $dbase);

	mysql_query($query)
		or die("couldn't query\n".mysql_error());

	disconnectDB($connection);
}
*/

/**********
 * assembleUpdateQuery()
 * used when updating address, email or phone information
 **********/
function assembleUpdateQuery( $tableUpdateInfo, $tableValueNum, $pid, $type ) {
	/** handle tableList table values **/

	$query = "UPDATE " . $tableUpdateInfo[0][0] . " SET ";

	for ( $i=0; $i<$tableValueNum-1; $i++ ) {
		$query .= $tableUpdateInfo[$i][1]. "='" . $tableUpdateInfo[$i][2]. "', ";
	}
	$query .= $tableUpdateInfo[$i][1]. "='" . $tableUpdateInfo[$i][2]. "'\n";

	if ( $pid && $type=="address") { $query .= "WHERE pid='" . $pid . "' AND addressType='" . $_POST['addressType'] . "'"; }
	if ( $pid && $type=="email" ) { $query .= "WHERE pid='" . $pid . "' AND emailType='" . $_POST['emailType'] . "'"; }
	if ( $pid && $type=="phone" ) { $query .= "WHERE pid='" . $pid . "' AND phoneType='" . $_POST['phoneType'] . "'"; }

// ?
	if ( $pid && $type=="involvement" ) { $query .= "WHERE pid='" . $pid . "' AND ministryID='" . $_POST['ministryID'] . "'"; }
	if ( $pid && $type=="interests" ) { $query .= "WHERE pid='" . $pid . "' AND interests='" . $_POST['interests'] . "'"; }
	

	return $query;
}


/**********
 * runUpdate() runs the assembled INSERT query
 **********
function runUpdate($query, $dbHost, $dbUser, $dbPassword, $dbase) {
	$connection = connectDB($dbHost, $dbUser, $dbPassword, $dbase);

	mysql_query($query)
		or die("couldn't query\n".mysql_error());

	disconnectDB($connection);
}
*/

/*************************
 * makeListHTML()
 * this function generates options for drop-down lists in a web form 
 * it accepts <1: a database query>, <2: array column number of data to be submitted>, 
 * <3: array column number of data seen by user>, <4, 5, 6, 7: database connection info>, 
 * <8: value of variable if form was already submitted - allows for fixing problems with 
 *     form instead of filling everything out again>
 *************************/
 
function makeListHTML ($query, $submitField, $displayField, $dbHost, $dbUser, $dbPassword, $dbase, $data) {

	$connection = connectDB($dbHost, $dbUser, $dbPassword, $dbase);

	// run query and get result
	$result = mysql_query($query)
		or die("couldn't query <br>\n".mysql_error());

	// from the $result, get the rows
	for ( $i=0; $i < mysql_num_rows($result); $i++ ) {
		$resultRowList[$i] = mysql_fetch_array($result);
	}

	disconnectDB($connection);


	// from the rows ($resultRowList), get the data and form the drop-down list option
	for ( $j=0; $j < count($resultRowList); $j++ ) {

		if ( $resultRowList[$j][$displayField] == $data ) $switch="selected"; else $switch=""; 

		// if the character "&" appears in the result, replace it with "&amp;" in HTML
		$theValue = $resultRowList[$j][$submitField];
		$displayed = $resultRowList[$j][$displayField];

		$theValue = str_replace("& ", "&amp; ", $theValue);
		$displayed = str_replace("& ", "&amp; ", $displayed);

		echo "\t\t\t<option value=\"" . $theValue . "\" " 
		. $switch . ">" . $displayed . "</option>\n";

	}

	// free variables used to hold data
	mysql_free_result($result);
	unset($resultRowList);

}


/*************************
 * makeCheckListHTML()
 *  
 * it accepts <1: a database query>, <2: array column number of data to be submitted>, 
 * <3: array column number of data seen by user>, <4, 5, 6, 7: database connection info>, 
 * <8: value of variable if form was already submitted - allows for fixing problems with 
 *     form instead of filling everything out again>
 * <9: name of variable>, <10: switch for showing NULL checkbox>
 *************************/
 
function makeCheckListHTML ($query, $submitField, $displayField, 
							$dbHost, $dbUser, $dbPassword, $dbase, 
							$data, $name, $showNULL) {

	$connection = connectDB($dbHost, $dbUser, $dbPassword, $dbase);

	// run query and get result
	$result = mysql_query($query)
		or die("couldn't query <br>\n".mysql_error());

	// from the $result, get the rows
	for ( $i=0; $i < mysql_num_rows($result); $i++ ) {
		$resultRowList[$i] = mysql_fetch_array($result);
	}

	disconnectDB($connection);


	// break lists into two columns
	$break = count($resultRowList)/2;
	
	// assures that either items in columns are even (no remainder) 
	// or first column has more items (remainder of 1)
	if ( $showNULL ) {
		if ( count($resultRowList)%2 ) $break = ceil($break); else $break = floor($break);
	} else {
		if ( count($resultRowList)%2 ) $break = ceil($break); else $break = floor($break)+1;
	}
	
	
	// switch to include NULL check option
	if ( $showNULL ) $j = 0; else $j = 1;
	
	// from the rows ($resultRowList), get the data and form the list of checkboxes
	for ( $j; $j < count($resultRowList); $j++ ) {

		$theValue = $resultRowList[$j][$submitField];
		$displayed = $resultRowList[$j][$displayField];

		if ( is_array($data) ) {
			foreach ( $data as $aMatch ) {
				if ( $displayed == $aMatch ) {
					$switch=" checked=\"checked\""; 
					break;
				} else {
					$switch="";
				}
			}
		}
			
		// if the character "&" appears in the result, replace it with "&amp;" in HTML
		$theValue = str_replace("& ", "&amp; ", $theValue);
		$displayed = str_replace("& ", "&amp; ", $displayed);

		// output list of checkboxes based on entries in table
		if ( $j == $break ) echo "\t\t\t</td>\n\t\t\t<td>\n";
		if ( $displayed == null ) $displayed = "[empty]";
			echo "\t\t\t<input type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $theValue . "\"" 
			. $switch . ">" . $displayed . "<br>\n";
	}
	
	// free variables used to hold data
	mysql_free_result($result);
	unset($resultRowList);

}


/**********
 * make salt for use by crypt (for passwords)
 **********/
function makesalt($type=CRYPT_SALT_LENGTH) {
	switch($type) {
		case 8:
		$saltlen = 9; $saltprefix = '$1$'; $saltsuffix = '$'; break;
		case 2:
		default: // by default, fall back on Standard DES (should work everywhere)
		$saltlen = 2; $saltprefix = ''; $saltsuffix = ''; break;
	}
	$salt='';
	while(strlen($salt) < $saltlen) $salt .= chr(rand(64,126));
	return $saltprefix.$salt.$saltsuffix;
}

?>
