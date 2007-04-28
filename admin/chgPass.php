<?php

session_start();

if ( !$_SESSION['validUser'] ) { header("Location: menu.php?display=menu"); exit; }

include_once('dataCheckFunctions.php');
require_once('dbFunctions.php');
require_once('DB.php');
require_once('htpasswdManager.php');
require_once('adminConstructFunctions.php');

$pageTitle = "change password";
$pageName = "chgPass";

printAdminHead($pageTitle, $pageName);
?>

<h1>Change your password</h1>

<p>Enter your current password, and then your new password twice.  All of these 
must follow the following criteria...<br /><br />
<ul>
<li><p>Passwords must be at least 8 characters long and 
may contain letters, digits, and the following characters: <br /><br />
_ - , . ! @ # $ % ) ( &amp; * = + : ; &lt; &gt; ? ~</li>
</ul>
</p>

<hr />

<?php if ( $_GET['step']=="process" ) $result=processChgPass(); echo $result; ?>
<br />

<p><a href="menu.php?display=menu">Cancel</a></p>

<div class="divBox">
<form name="addUser" method="post" action="chgPass.php?step=process">
	<h2>User information:</h2><br />

	<table>
	<tr><td>Current password: </td><td></td><td><input name="passwd" type="password" class="inputBox" /></td></tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr><td>New password: </td><td></td><td><input name="newpass" type="password" class="inputBox" /></td></tr>
	<tr><td>New password (again): </td><td></td><td><input name="newpass2" type="password" class="inputBox" /></td></tr>
	</table><br /><br />

	<input name="submit" type="submit" value="change password" class="button" />
</form>
</div>

<p><a href="menu.php?display=menu">Cancel</a></p><br />

<?php
printAdminBottom($pageName);


// check submitted values and either process adding the user, 
// or display the appropriate error message
function processChgPass () {
	$dsn = unserialize(DSN);

	// make sure all fields had values submitted
	if ( $_POST['passwd'] && $_POST['newpass'] && $_POST['newpass2']  ) {
		// check that these are all valid passwords
		if ( checkPassword($_POST['passwd']) && checkPassword($_POST['newpass']) && checkPassword($_POST['newpass2']) ) {

			$db =& DB::connect($dsn);
			if (PEAR::isError($db)) { die($db->getMessage()); }

			$query = "SELECT passwd, iv, htpass FROM users WHERE userName='".$_SESSION['validUser']."'";
			$res =& $db->query($query);
			if (PEAR::isError($res)) { die($res->getMessage()); }

			$row =& $res->fetchRow();
			if (PEAR::isError($row)) { die($row->getMessage()); }
			$res->free();

			$db->disconnect();

			$encryption = array();	// $encryption receives the encrypted string and the iv
			$encryption = encryptString($_POST['passwd'], $row[1]);

			// encrypted "current password" matches password stored in db
			if ( $encryption[0] == $row[0] ) {

				// verify that the two new passwords are identical
				if ( $_POST['newpass'] == $_POST['newpass2'] ) {

					$encryption = array();	// $encryption receives the encrypted string and the iv
					$encryption = encryptString($_POST['newpass']);

					// if htpass was set, update it
					if ( $row[2] != null ) $htpass = ", htpass='".makePasswd($_POST['newpass'])."'"; else $htpass = '';

					// query for changing the saved password to the new password
					$updatePassword = "UPDATE users SET passwd='".$encryption[0]."', "
							. "iv='".$encryption[1]."'" . $htpass 
							. " WHERE userName='".$_SESSION['validUser']."'";

					// run query - returns TRUE if successful
					if ( commandQuery($updatePassword) ) {
						$displayMessage = '<p class="confirmation">Password successfully changed.</p>'."\n";

						if ( $row[2] != null ) $updated_htpass = make_htpasswd();	// update .htpasswd file
						if ( $updated_htpass ) {	// update successful
							$displayMessage .= '<p class="confirmation">.htpasswd file updated successfully.</p>'."\n";
						} else {	// update failed
							$displayMessage .= '<p class="error">.htpasswd file could not be updated.</p>'."\n";
						}

						$_GET['step'] = "ready";	// change mode of page so it won't try to process again
					} else {
						$displayMessage = '<p class="error">Error: Password change failed.</p>'."\n";
					}
				} else {
					$displayMessage = '<p class="error">Error: The new passwords do not match.</p>'."\n";
				}
			} else {
				$displayMessage = '<p class="error">Error: The <strong><em>current password</em></strong> you entered is not correct.</p>'."\n";
			}
		} else {
			$displayMessage = '<p class="error">Error: At least one of the entered passwords was not valid.  Please verify that you follow the guidelines for passwords.</p>'."\n";
		}
	} else {
		$displayMessage = '<p class="error">Error: Not all values were entered.</p>'."\n";
	}

	return $displayMessage;
}

?>