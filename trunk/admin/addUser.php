<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('ADMINISTRATOR', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

include_once('dataCheckFunctions.php');
require_once('dbFunctions.php');
require_once('DB.php');
require_once('htpasswdManager.php');
require_once('adminConstructFunctions.php');

$pageTitle = "add user";
$pageName = "addUser";

printAdminHead($pageTitle, $pageName);
?>

<h1>Add a user to the system</h1>

<p>This form allows you to create a new user account.  Users can log in to the website to perform 
tasks that they have permission to do.  All fields are required.<br /><br />
<ul>
<li><p><strong><em>First name</em></strong> and <strong><em>Last name</em></strong> must be composed of letters or the characters: _ . -</p></li>
<li><p><strong><em>User ID</em></strong> must be composed of letters, digits, or the characters: _ . -</p></li>
<li><p><strong><em>Password</em></strong> must be at least 8 characters long and may contain letters, digits, and the following characters: <br /><br />_ - , . ! @ # $ % ) ( &amp; * = + : ; &lt; &gt; ? ~</li>
</ul>
</p>

<hr />

<?php if ( $_GET['step']=="process" ) $result=processAddUser(); echo $result; ?>
<br />

<p><a href="manageUsers.php">Cancel</a></p>

<div class="divBox">
<form name="addUser" method="post" action="addUser.php?step=process">
	<h2>User information:</h2><br />

	<table>
	<tr><td>First name: </td><td></td><td><input name="fname" type="text" class="inputBox" value="<?php if ($_GET['step']=="process") echo $_POST['fname']; ?>" maxlength="25" /></td></tr>
	<tr><td>Last name: </td><td></td><td><input name="lname" type="text" class="inputBox" value="<?php if ($_GET['step']=="process") echo $_POST['lname']; ?>" maxlength="25" /></td></tr>
	<tr><td>Email: </td><td></td><td><input name="email" type="text" class="inputBox" value="<?php if ($_GET['step']=="process") echo $_POST['email']; ?>" maxlength="50" /></td></tr>
	<tr><td colspan="3"><br /></td></tr>
	<tr><td>Username: </td><td></td><td><input name="userName" type="text" class="inputBox" value="<?php if ($_GET['step']=="process") echo $_POST['userName']; ?>" maxlength="25" /></td></tr>
	<tr><td>Password: </td><td></td><td><input name="passwd" type="password" class="inputBox" maxlength="50" /></td></tr>
	<tr><td>Password (again): </td><td></td><td><input name="passwd2" type="password" class="inputBox" maxlength="50" /></td></tr>
	</table><br />

	<h2>User permissions:</h2><br />
<?php
$permissionsArray = showPermissions();	// array of available permissions
if ( $permissionsArray ) {
	echo "\t".'<ul class="selectionList">'."\n";
	for ($i=0; $i<count($permissionsArray); $i++) {
		echo "\t".'<li><input type="checkbox" name="permissions[]" value="'.$permissionsArray[$i][0].'" ';

		// check the appropriate boxes
		if ( in_array($permissionsArray[$i][0], $_POST['permissions']) && $_GET['step']=="process" ) {
			echo 'checked="checked" ';
		}

		echo '/> <strong>'.$permissionsArray[$i][1].'</strong>: '.$permissionsArray[$i][2]."</li>\n";
	}
	echo "\t</ul>\n";
}
?>
	<br />

	<h2>Make user account active?</h2>
	<p>
	<input type="radio" name="status" value="active" /> <span class="confirmation"><strong>yes</strong></span> &nbsp;
	<input type="radio" name="status" value="pending" checked="checked" /> <span class="error"><strong>no</strong></span>
	</p>
	<hr />

	<p><input name="submit" type="submit" value="add user" class="button" /></p>
</form>
</div>

<p><a href="manageUsers.php">Cancel</a></p><br />

<?php
printAdminBottom($pageName);


// check submitted values and either process adding the user, 
// or display the appropriate error message
function processAddUser () {
	// make sure all fields had values submitted
	if ( $_POST['userName'] && $_POST['email'] && $_POST['passwd'] && $_POST['passwd2'] && $_POST['permissions'] && $_POST['fname'] && $_POST['lname'] ) {
		// make sure password fields match
		if ( $_POST['passwd'] == $_POST['passwd2'] ) {
			// check that the values submitted are valid
			if ( isAlphaNumeric($_POST['userName']) && checkEmail($_POST['email']) && checkPassword($_POST['passwd']) && isLetters($_POST['fname']) && isLetters($_POST['lname']) ) {
//				$salt = makesalt();
//				$password = crypt($_POST['passwd'], $salt);

				$encryption = array();	// $encryption receives the encrypted string and the iv
				$encryption = encryptString($_POST['passwd'], $row[2]);
				$htpass = makePasswd($_POST['passwd']);	// makes the password to use in .htpasswd

				if ( $_POST['status'] == 'active' ) {
					$status = 'active';
				} else {
					$status = 'pending';
				}

				$dsn = unserialize(DSN);	// connection info for use in PEAR
				$db =& DB::connect($dsn);	// connect to database
				$sth = $db->prepare('INSERT INTO users (userName, passwd, htpass, fname, lname, iv, email, accountStatus) VALUES (?,?,?,?,?,?,?,?)');
				$data = array($_POST['userName'], $encryption[0], $htpass, $_POST['fname'], $_POST['lname'], $encryption[1], $_POST['email'], $status);
				$res =& $db->execute($sth, $data);	// insert the user
				$db->disconnect();	// disconnect from the database

				if ( PEAR::isError($res) ) {
					$userExists = getOne("SELECT COUNT(userName) FROM users WHERE userName='".$_POST['userName']."'");
					if ( $userExists == 1 ) {	// userName is already in the system
						$displayMessage = '<p class="error">Error: This username already exists in the system.  Choose a different username.</p>'."\n";
					} else {	// some other error occured
						$displayMessage = '<p class="error">There was an error while trying to add this user to the database.</p>'."\n";
					}
				} else {	// successful
					// display account creation confirmation message
					$displayMessage = '<p class="confirmation">Account successfully created for user <strong>'.$_POST['userName'].'</strong>.</p>'."\n";

					// get the uid of the user that was just inserted
					$uid = getOne("SELECT uid FROM users WHERE userName='".$_POST['userName']."'");

					// for the new user, insert their granted permissions
					for ($i=0; $i<count($_POST['permissions']); $i++) {
						$permissionsQuery = "INSERT INTO user_permissions SET uid='".$uid."', "
										. "permissionID='".$_POST['permissions'][$i]."'";

						if ( !commandQuery($permissionsQuery) ) { echo '<p>Error inserting permissions data</p>'."\n"; exit; }

						// get any .htpasswd files associated with this user's permissions
						$htPassQuery = "SELECT permissionName, htpasswdLocation FROM permissions WHERE permissionID='".$_POST['permissions'][$i]."'";
						$res = returnQuery($htPassQuery);
						$htpassData =& $res->fetchRow();

						if ( $htpassData[1] ) {	// htpasswdLocation
							// add this user to the .htpasswd file
							$added_htpass = addTo_htpasswd($htpassData[1], $_POST['userName'], $htpass);

							if ( $added_htpass ) {	// user was added to the .htpasswd file
								$displayMessage .= '<p class="confirmation">User successfully added to .htpasswd file for <strong>'.strtoupper($htpassData[0]).'</strong></p>'."\n";
							} else {	// user should have been added to .htpasswd, but wasn't
								$displayMessage .= '<p class="error">User was not added to .htpasswd file for <strong>'.strtoupper($htpassData[0]).'</strong></p>'."\n";
							}
						}
					}

					$_GET['step'] = "ready";	// change mode of page so it won't try to process again
				}
			} else {
				$displayMessage = '<p class="error">Error: There was a problem with at least one of the values you entered.  Please verify that you follow the guidelines for all of the fields</p>'."\n";
			}
		} else {
			$displayMessage = '<p class="error">Error: The two password fields do not match.  Please try entering your password in both fields again.</p>'."\n";
		}
	} else {
		$displayMessage = '<p class="error">Error: Some data was missing.  Before submitting the form, the <em>first name</em>, <em>last name</em>, <em>username</em>, <em>email</em>, and both <em>password</em> fields must be filled in and at least one <em>permissions level</em> chosen.</p>'."\n";
	}

	return $displayMessage;
}

?>