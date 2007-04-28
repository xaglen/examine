<?php

session_start();

if ( $_SESSION['validUser'] ) { header("Location: menu.php?display=menu"); exit; }

require_once('dataCheckFunctions.php');
require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('../pageComponents/pageConstructFunctions.php');

$pageTitle = "login";
$pageName = "login";
$layoutType = "openWindow";	// leftMenu, openWindow
$path = SYSTEM_BASE."login/";


// get submitted username and password
//$username = $_POST['userName'];
//$password = $_POST['passwd'];

$error = "";

if ( $_POST['userName'] && $_POST['passwd'] ) {

	// query database to check login info
	if ( isAlphaNumeric($_POST['userName']) && checkPassword($_POST['passwd']) ) {

		$db =& DB::connect($dsn);
		if (PEAR::isError($db)) { die($db->getMessage()); }

		$query = "SELECT passwd, iv, accountStatus FROM users WHERE userName='".$_POST['userName']."'";
		$res =& $db->query($query);
		if (PEAR::isError($res)) { die($res->getMessage()); }

		$row =& $res->fetchRow();
		if (PEAR::isError($row)) { die($row->getMessage()); }
		$res->free();

		$db->disconnect();

		if ( $row[2] == 'active' ) {
			// if submitted password matches saved password then assign the username as a 
			// valid user for the session (password is saved in the database encrypted)
/*			$type = CRYPT_SALT_LENGTH;
			switch($type) {
				case 8:
				$salt = substr($row[0],0,8); break;
				case 2:
				default: // by default, fall back on Standard DES (should work everywhere)
				$salt = substr($row[0],0,2); break;
			}
*/
			$encryption = array();	// $encryption receives the encrypted string and the iv
			$encryption = encryptString($_POST['passwd'], $row[1]);

			if ( $encryption[0] == $row[0] ) {
// 			if ( crypt($_POST['passwd'], $salt) == $row[0] ) {
				$_SESSION['validUser'] = $_POST['userName'];
				$_GET['step'] = "";
				header("Location: menu.php?display=menu"); exit;
			} else {
				$error = "Login failed - the user name and password you entered do not match an existing account."; 
			}
		} else {
			$error = 'This user account is not activated.'."\n";
		}
	} else {
		$error = "Login failed - the user name and/or password contain disallowed characters.";
	}
} elseif ( $_GET['step']=="process" ) {
	$error = "Login failed - the user name or password was empty.";
}

printPageTop($pageTitle, $pageName, $layoutType);
?>
<div id="winTop"></div>
<div id="winContent">
<div id="content">
<script language="JavaScript">
function placeFocus() {
	if (document.forms.length > 0) {
		var field = document.forms[0];
		for (i = 0; i < field.length; i++) {
			if ( (field.elements[i].type == "text") || (field.elements[i].type == "textarea") || (field.elements[i].type.toString().charAt(0) == "s") ) {
				document.forms[0].elements[i].focus();
				break;
			}
		}
	}
}
window.onload = placeFocus;
</script>

	<h1>:: login</h1>

	<p>Authorized users may log in to gain access to certain privileges.</p>

	<? if ($_GET['step']=="process") echo '<p class="error">'.$error.'</p>'; ?>
	
	<div class="special">
	<form name="login" method="post" action="index.php?step=process">
	<table>
	<tr>
		<td class="label">User Name:</td>
		<td><input name="userName" type="text" size="25" class="inputBox"></td>
	</tr>
	<tr>
		<td class="label">Password:</td>
		<td><input name="passwd" type="password" size="25" class="inputBox"></td>
	</tr>
	<tr>
		<td></td>
		<td><input name="submit" type="submit" value="Login" class="button"></td>
	</tr>
	</table>
	</form>
	</div>
</div> <!-- end content -->
</div> <!-- end winContent -->
<div id="winBottom"></div>
<?php

printPageBottom($pageName);

?>