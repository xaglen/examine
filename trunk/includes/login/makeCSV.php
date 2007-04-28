<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('DATABASE', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('dataCheckFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "Make a CSV file";
$pageName = "makeCSV";
$path = SYSTEM_BASE."login/";


// get all of the tables in the database
$tables = array();
$tables = getTables();

if ($_GET['step'] == 'process') {
	// check data
	if (isAlphaNumeric($_POST['fileName']) && $_POST['fileName'] != ".csv" && $_POST['fileName'] && $_POST['table'] ) {
		// make CSV file
		tableToCSV($_POST['table'], $_POST['fileName']);
		
		exit;
	} else {
		$_GET['step'] = 'error';
	}
}

printAdminHead($pageTitle, $pageName);
?>
<h1>Make a CSV file from a data table</h1>

<p>
Convert a database table into a CSV file.  Select a table from the list 
and then enter a name for the file (including the .csv extension).  You 
may want to indicate the date (and time) of the file's creation in the 
name you choose.
</p>

<?php
if ( $_GET['step'] == 'confirm' ) {
	echo '<p class="confirmation">You should have been prompted to save the file.</p>';
} elseif ( $_GET['step'] == 'error' ) {
	echo '<p class="error">There was an error. You must select a table and enter a valid file name.</p>';
}
?>

<p><a href="manageDB.php">Cancel</a></p>

<form method="post" action="makeCSV.php?step=process">
<div class="divBox">
<h2>Tables in the database:</h2>
<br />
<?php
// list the tables
echo '<table class="dataDisp">'."\n";
for ($i=0; $i<count($tables); $i++) {
	if ( $i%2 == 0 ) $class = ' class="data"'; else $class = ' class="dataEven"';	// alternate background color of rows
	echo '<tr'.$class.'>'."\n";
	echo '<td><input type="radio" name="table" value="'.$tables[$i].'" />'.$tables[$i].'</td>'."\n";
	echo '</tr>'."\n";
}
echo '</table>'."\n";

?>
<br />
<hr />

<p><strong>Name of file:</strong> <input type="text" name="fileName" value="<?php if ($_POST['fileName']) echo $_POST['fileName']; else echo '.csv'; ?>" class="inputBox" /><br />
NOTE: name may only contain letters, digits, and the following characters: &nbsp; - &nbsp; _ &nbsp; .
</p>

<p><input type="submit" name="submit" value="Make CSV file" class="button" /></p>
</div>

</form>

<p><a href="manageDB.php">Cancel</a></p>
<?php

printAdminBottom($pageName);

?>
