<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('DATABASE', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "database management tools";
$pageName = "manageDB";
$path = SYSTEM_BASE."login/";


printAdminHead($pageTitle, $pageName);
?>
<h1>Database management</h1>

<p><a href="menu.php?display=menu">Cancel</a></p>

<div class="divBox">
<h2>Tools:</h2>
<ul>
<li><p><a href="<?php echo ADMIN.'makeCSV.php'; ?>">Make a CSV file</a> - output a database 
table as a CSV file (viewable in Excel).  NOTE: all data remains intact in the database</p></li>

<li><p><a href="<?php echo ADMIN.'insertFromCSV.php'; ?>">Insert data from a CSV file</a> - 
<strong>inserts</strong> all of the data from a CSV file.  This functionality is intended 
for initially populating a data table.</p></li>
</ul><br />
</div>

<p><a href="menu.php?display=menu">Cancel</a></p>

<?php

printAdminBottom($pageName);

?>