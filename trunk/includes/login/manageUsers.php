<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('ADMINISTRATOR', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "manage users";
$pageName = "manageUsers";
$path = SYSTEM_BASE."login/";


printAdminHead($pageTitle, $pageName);
?>
<h1>Manage users</h1>

<p><a href="menu.php?display=menu">Cancel</a></p>

<div class="divBox">
<h2>Functions:</h2>
<ul>
<li><p><a href="<?php echo ADMIN; ?>addUser.php"><strong>Add</strong> a new user to the system</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>delUser.php"><strong>Remove</strong> a user from the system</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>modUser.php"><strong>Change</strong> a user's permissions</a></p></li>
</ul><br />
</div>

<p><a href="menu.php?display=menu">Cancel</a></p>
<?php

printAdminBottom($pageName);

?>