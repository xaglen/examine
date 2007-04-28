<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('CONTENT', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "page content";
$pageName = "manageContent";
$path = SYSTEM_BASE."login/";


printAdminHead($pageTitle, $pageName);
?>
<h1>Webpage content</h1>

<p><a href="menu.php?display=menu">Cancel</a></p>

<div class="divBox">
<h2>Functions:</h2>
<ul>
	<li><p><a href="<?php echo ADMIN; ?>newContentEntry.php"><strong>Add</strong> new content</a></p></li>
	<li><p><a href="<?php echo ADMIN; ?>updateContentEntry.php"><strong>Update</strong> content</a></p></li>
</ul>
</div>

<p><a href="menu.php?display=menu">Cancel</a></p>

<?php

printAdminBottom($pageName);

?>