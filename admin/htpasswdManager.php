<?php

session_start();

if ( !$_SESSION['validUser'] ) { header("Location: menu.php?display=menu"); exit; }

require_once('adminConstructFunctions.php');
require_once('dbFunctions.php');
require_once('DB.php');

//require_once('authorizationFunctions.php');

//define('HTPASSWD_FILE', '/home/chialpha/dbAdmin/.htpasswd');	// location of htpasswd file (NOT in Web path)


//
// need_htpass()
// Compiles a list of the permissions that have
//
function need_htpass () {
	$query = "SELECT permissionID, permissionName FROM permissions WHERE htpasswdLocation IS NOT NULL";
	$res = returnQuery($query);
	while ( $row =& $res->fetchRow() ) {
		$array[] = array($row[0], strtoupper($row[1]));
	}
	$res->free();

	return($array);
//	getPermissionLevels();
//	print_r(unserialize(PERMISSION_LEVELS));break;
}



//
// newUserForm()
// Displays a form to enter a new user
//
function newUserForm () {
?>
<div class="divBox">
<h2>add user</h2><br />
<form action="htpasswdManager.php?do=newUser" method="post">
<table>
<tr>
	<td><strong>First name: </strong></td>
	<td><input type="text" name="fname" length="25" maxlength="25" value="<?php echo $_POST['fname']; ?>" /></td>
</tr>
<tr>
	<td><strong>Last name: </strong></td>
	<td><input type="text" name="lname" length="25" maxlength="25" value="<?php echo $_POST['lname']; ?>" /></td>
</tr>
<tr>
	<td><strong>Username: </strong></td>
	<td><input type="text" name="username" length="25" maxlength="25" value="<?php echo $_POST['username']; ?>" /></td>
</tr>
<tr>
	<td><strong>Password: </strong></td>
	<td><input type="password" name="password" length="25" maxlength="25" value="<?php echo $_POST['password']; ?>" /> * must be six characters or longer</td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="New User" class="button" /></td>
</tr>
</table>
</form>
</div>
<?php
}



//
// processNewUser()
// Processes newUserForm, either returning error messages or entering a new
// user into the database and updating the .htpasswd file
//
function processNewUser () {
	// validate entries
	if ( !validText($_POST['fname']) && $_POST['fname'] ) $msg[] = 'First name is not valid';
	if ( !validText($_POST['lname']) && $_POST['lname'] ) $msg[] = 'Last name is not valid';
	if ( !validText($_POST['username']) && $_POST['username'] ) $msg[] = 'Username is not valid';
	if ( !looseText($_POST['password']) || strlen($_POST['password']) < 6 ) $msg[] = 'Password is not valid';

	// check that user doesn't already exist
	$selectQuery = "SELECT count(*) FROM users WHERE username='".$_POST['username']."'";

	$matches = returnQuery($selectQuery);
	if ( $matches === false ) $msg[] = 'There was an error communicating with the database';
	elseif ( $matches !== 0 ) $msg[] = 'Username is already taken';

	if ( $msg ) return $msg;

	// submit data to db
	$insertQuery = "INSERT INTO users SET fname='".$_POST['fname']."', lname='".$_POST['lname']."', "
			. "username='".$_POST['username']."', password='".makePasswd($_POST['password'])."'";

	if ( commandQuery($insertQuery) ) {
		$success = make_htpasswd();	// update .htpasswd
		if ( $success ) return 'success';
		else return 'fail';
	}
}



//
// makePasswd()
// Encrypts a password before it is stored in the database or in the .htpasswd file
function makePasswd ( $pass ) {
	$pass = crypt(trim($pass), base64_encode(CRYPT_STD_DES)); 
	return $pass; 
}



//
// addTo_htpasswd()
// Appends the supplied user/password info to .htpasswd
//
function addTo_htpasswd ( $file, $userID, $pass ) {
	if ( is_writable($file) ) {
		// open the .htaccess file and write the newest data to it
		if ( !$handle = fopen($file, "a") ) {
			echo '<p class="error">Cannot open file ('.$file.')</p>'."\n";
			return false;
		}

		$line = $userID.':'.$pass."\n";
		if ( fwrite($handle, $line) === FALSE ) {
			echo '<p class="error">Could not update file ('.$file.')</p>'."\n";
			return false;
		}

		fclose($handle);
		return true;
	} else {
		echo '<p class="error">The file '.$file.' is not writable</p>'."\n";
		return false;
	}
}



//
// make_htpasswd()
// Reads the database and rewrites the updated user/password info to .htpasswd files
//
function make_htpasswd () {
	$res = returnQuery("SELECT htpasswdLocation FROM permissions WHERE htpasswdLocation IS NOT NULL");
	while ( $htpassData =& $res->fetchRow() ) {
		$file = $htpassData[0];

		$userData = getUserInfo($file);

		// if error retrieving data, stop the process (don't change the .htaccess files)
		if ( !$userData ) return false;

		if ( is_writable($file) ) {
			// open the .htpasswd file and erase its contents
			if ( !$handle = fopen($file, "w") ) {
				echo '<p class="error">Cannot open file ('.$file.')</p>'."\n";
			}

			// write the user data
			for ($i=0; $i<count($userData[0]); $i++) {
				$line = $userData[0][$i].':'.$userData[1][$i]."\n";
				fwrite($handle, $line);
			}

			fclose($handle);
		} else {
			echo '<p class="error">The file '.$file.' is not writable</p>'."\n";
		}
	}

	// open the .htpasswd file, erase its contents and write the newest data to it
/*	$handle = fopen($file, "w");
	for ($i=0; $i<count($userData[0]); $i++) {
		$line = $userData[0][$i].':'.$userData[1][$i]."\n";
		fwrite($handle, $line);
	}
	fclose($handle);
*/
	return true;
}



//
// getUserInfo()
// Gets username/password data to use in .htpasswd
//
function getUserInfo ($file) {
	$selectQuery = "SELECT userName, htpass FROM users, user_permissions, permissions "
				. "WHERE permissions.htpasswdLocation='".$file."' AND permissions.permissionID=user_permissions.permissionID "
				. "AND user_permissions.uid=users.uid";

	$res = returnQuery($selectQuery);

	if ( !$res ) return false;

	while ($res->fetchInto($row)) {	// collect the data
		if ( $row[1] ) {
			$username[] = $row[0];
			$password[] = $row[1];
		}
	}

	return(array($username, $password));
}

?>