<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('EMAIL', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

include_once('dataCheckFunctions.php');
include_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "add email address to list";
$pageName = "addEmail";

if ( $_GET['action'] == "restore" ) $message = restoreEmail();

printAdminHead($pageTitle, $pageName);
?>

<h1>Restore an email address to the mailing list</h1>

<p>Enter the email address and then click the <em>restore email</em> button at the bottom of the page.</p>

<?php
echo $message;
?>

<p><a href="menu.php?display=menu">Cancel</a></p>

<hr /><br /><br />

<form action="restoreEmail.php?action=restore" method="post">
<table>
<tr>
<td>email address: </td>
<td></td>
<td><input type="text" name="email" /></td>
</tr>
<tr>
<td colspan="2"></td>
<td><input class="button" type="submit" value="restore email" /></td>
</tr>
</table>
</form>

<br />
<hr />

<p><a href="menu.php?display=menu">Cancel</a></p><br />

<?php
printAdminBottom($pageName);


function restoreEmail () {
	$update = "UPDATE emailList SET okToEmail='1' WHERE email='".$_POST['email']."'";

	if ( commandQuery($update) === false ) {
		$message = '<p class="error">Could not restore this email.</p>';
	} else {
		$message = '<p class="confirmation">Email restored!</p>';
	}

	return $message;
}

?>