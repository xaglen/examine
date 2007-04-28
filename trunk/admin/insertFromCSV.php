<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('DATABASE', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('dataCheckFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "Insert data from a CSV file";
$pageName = "insertFromCSV";
$path = SYSTEM_BASE."login/";


if ( $_GET['step'] == 'process' ) {
	// check user input
	if ( $_POST['fileName'] != ".csv" && $_POST['fileName'] && $_POST['table'] && $_POST['colHeadings'] && $_POST['numCols'] ) {
		// insert data
		csvToTable($_POST['table'], $_POST['colHeadings'], $_POST['fileName'], $_POST['numCols']);
		$_GET['step'] = 'confirm';
	} else {
		$_GET['step'] = 'error';
	}
}

printAdminHead($pageTitle, $pageName);
?>
<h1>Insert data from a CSV file</h1>

<p>
<strong><span class="error">IMPORTANT:</span></strong> This function takes the data from a comma-delimited 
.csv file and <strong>inserts</strong> it into the selected database table.  The 
table must already exist in the database and the data must be formatted correctly 
for the table.  This script does not perform extensive error checking.  It is 
intended for initially filling a new table with a large amount of data already 
existing in a spreadsheet.
</p>

<?php
if ( $_GET['step'] == 'confirm' ) {
	echo '<p class="confirmation">Data was inserted</p>'."\n\n";
} elseif ( $_GET['step'] == 'error' ) {
	echo '<p class="error">There was an error. You must enter valid table and file names, select one of the heading options and enter the number of columns.</p>';
}
?>

<p><a href="manageDB.php">Cancel</a></p>

<form method="post" action="insertFromCSV.php?step=process">
<div class="divBox">
<h2>Table and file:</h2>
<br />
<p><strong>Database table:</strong> <input type="text" name="table" class="inputBox" /></p>

<hr />

<p>
<strong>Column headings:</strong><br />
<input type="radio" name="colHeadings" value="yes" /> CSV <strong>does</strong> contain column titles (first row)<br />
<input type="radio" name="colHeadings" value="no" /> CSV <strong>does not</strong> contain column titles
</p>

<p>
<strong>Number of columns:</strong>
<input type="text" name="numCols" size="5" maxlength="4"  class="inputBox" /> (# of columns of data to insert from the .csv)
</p>

<hr />

<p>
<strong>Filename:</strong> <input type="text" name="fileName" value="<?php if ($_POST['fileName']) echo $_POST['fileName']; else echo '.csv'; ?>" class="inputBox" />
<br />
NOTE: This file must exist on the server (include the full path).
<!--The name may only contain letters, digits, and the following characters: &nbsp; - &nbsp; _ &nbsp; .-->
</p>

<p><input type="submit" name="submit" value="Insert data" class="button" /></p>
</div>

</form>

<p><a href="manageDB.php">Cancel</a></p>
<?php

printAdminBottom($pageName);

?>