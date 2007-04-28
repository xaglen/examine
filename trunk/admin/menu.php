<?php

session_start();

if ( !$_SESSION['validUser'] ) { header("Location: .."); exit; }

require_once('adminConstructFunctions.php');
require_once('../pageComponents/definitions.php');
require_once('authorizationFunctions.php');

$pageTitle = "menu";
$pageName = "menu";

// call function that defines permission level constants
getPermissionLevels();

// get permissions for this user
getUserPermissions($_SESSION['validUser']);


printAdminHead ($pageTitle, $pageName);
?>

<h1>Menu</h1>

<p>&gt;&gt; You are logged in as <strong><?php echo $_SESSION['validUser']; ?></strong></p>

<ul>
<!--li><p><a href="">Change your <strong>username</strong></a></p></li-->
<li><p><a href="<?php echo ADMIN; ?>chgPass.php">Change your <strong>password</strong></a></p></li>
</ul><br />

<?php
if ( in_array('CONTENT', $_SESSION['permissions']) ) {
//if ( $_SESSION['clearance'] <= 3 ) {
?>
<div class="divBox">
<h2>Webpage Content</h2>
<ul>
<li><p><a href="<?php echo ADMIN; ?>newContentEntry.php"><strong>Add</strong> new content</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>updateContentEntry.php"><strong>Update</strong> content</a></p></li>
<!--<li><p><a href="<?php echo ADMIN; ?>delUser.php"><strong>Remove</strong> a user from the system</a></p></li>-->
</ul>
</div><br />
<?php
}

if ( in_array('EMAIL', $_SESSION['permissions']) ) {
//if ( $_SESSION['clearance'] < 3 ) {
?>
<div class="divBox">
<h2>Email</h2>
<ul>
<!--<li><p><a href="<?php echo ADMIN; ?>email2.php"><strong>Compose email</strong></a></p></li>-->
<li><p><a href="<?php echo ADMIN; ?>email_plain.php">Compose <strong>plain text email</strong></a></p></li>
<!--<li><p><a href="<?php echo ADMIN; ?>email_html.php">Compose <strong>html email</strong></a></p></li>-->
<li><p><a href="<?php echo ADMIN; ?>addEmail.php"><strong>Add email</strong> to mailing list</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>restoreEmail.php"><strong>Restore an email</strong> to the list</a></p></li>
</ul>
</div><br />
<?php
} 

if ( in_array('ADMINISTRATOR', $_SESSION['permissions']) ) {
//if ( $_SESSION['clearance'] == 1 ) {
?>
<div class="divBox">
<h2>Manage Users</h2>
<ul>
<li><p><a href="<?php echo ADMIN; ?>addUser.php"><strong>Add</strong> a new user to the system</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>delUser.php"><strong>Remove</strong> a user from the system</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>modUser.php"><strong>Change</strong> a user's permissions</a></p></li>
</ul>
</div><br />
<?php
}

if ( in_array('DATABASE', $_SESSION['permissions']) ) {
//if ( $_SESSION['clearance'] == 1 ) {
?>
<div class="divBox">
<h2>Database</h2>
<ul>
<li><p><a href="<?php echo ADMIN; ?>makeCSV.php"><strong>Make</strong> a CSV file</a></p></li>
<li><p><a href="<?php echo ADMIN; ?>insertFromCSV.php"><strong>Insert data from</strong> a CSV file</a></p></li>
</ul>
</div><br />
<?php
}

printAdminBottom ($pageName);

?>