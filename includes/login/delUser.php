<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('ADMINISTRATOR', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbFunctions.php');
require_once('DB.php');
require_once('htpasswdManager.php');
require_once('adminConstructFunctions.php');
require_once('../pageComponents/formTools.php');

$pageTitle = "remove user";
$pageName = "delUser";

printAdminHead($pageTitle, $pageName);
?>

<h1>Remove a user from the system</h1>

<p>Select the user you wish to delete from the system and then click the <em>Submit</em> 
button at the bottom of the page.  You may not remove yourself.  When you delete a user 
there is not an <em>undo</em> option.  If you make a mistake, you will have to create a 
<a href="addUser.php">new account</a> for the person.</p>

<p><a href="manageUsers.php">Cancel</a></p>

<?php

	if ( $_GET['step'] == 'process' ) {
		if ( $_POST['user'] ) {
			$result = deleteUser(); 
			echo $result;
		} else {
			echo '<p class="error">Error: You must select a user to delete.</p>';
		}
	}
?>

<div class="divBox">
<h2>Current users:</h2><br />
<?php showUsers(); ?>
</div>

<p><a href="manageUsers.php">Cancel</a></p>

<?php
printAdminBottom($pageName);

//functions

function showUsers () {
	// set default for LIMIT clause in query
	$limitNum = 20;
	$limit = ' LIMIT ' . $limitNum;	// number of results for a page

	// check for page (show older posts)
	if ( onlyDigits($_GET['page']) && $_GET['page'] != null ) {
		$page = $_GET['page'];
		if ( $page > 1 ) {	// page 2 or above
			$skip = ($page-1) * $limitNum;
			$limit = ' LIMIT ' . $skip . ',' . $limitNum;	// skip results from previous pages and get this page's 25 results
		}
	}

	$userQuery = "SELECT uid, userName, fname, lname, accountStatus FROM users ORDER BY lname, fname".$limit;
	$userData = returnQuery($userQuery);
	$rowNum = 1;
?>
<form method="post" action="delUser.php?step=process">
<table class="dataDisp">
<tr>
	<th class="radio"></th>
	<th><strong>Name</strong></th>
	<th><strong>Username</strong></th>
	<th><strong>Permissions</strong></th>
	<th><strong>Status</strong></th>
</tr>
<?php
	if ( $userData ) {
		// display a row for each user, allowing only one to be selected for modification
		while ( $user =& $userData->fetchRow() ) {
			if ( $rowNum%2 != 0 ) $class="data"; else $class="dataEven";	// alternate background color
			if ( $_SESSION['validUser'] != $user[1] ) $radioButton = '<input type="radio" name="user" value="'.$user[1].'" />'; else $radioButton = '';	// do not allow a user to delete himself
			echo '<tr class="'.$class.'">'."\n";
			echo "\t".'<td class="radio">'.$radioButton.'</td>'."\n";	// radiobutton selection
			echo "\t".'<td>'.$user[2].'&nbsp;'.$user[3].'</td>'."\n";	// Name column
			echo "\t".'<td>'.$user[1].'</td>'."\n";	// Username column

			// Permissions column
			$permissionQuery = "SELECT permissionName, permissionDesc FROM permissions, user_permissions "
						. "WHERE user_permissions.uid='".$user[0]."' AND permissions.permissionID=user_permissions.permissionID";

			echo "\t".'<td>';
			$permissionData = returnQuery($permissionQuery);
			while ( $permissions =& $permissionData->fetchRow() ) {
				echo strtoupper($permissions[0]).'<br />';	// permission name
			}
			echo '</td>'."\n";

			// user account status (active/pending)
			if ( $user[4] == 'active' ) {
				echo "\t".'<td class="confirmation"><strong>active</strong></td>'."\n";
			} else {
				echo "\t".'<td class="error"><strong>pending</strong></td>'."\n";
			}

			echo '</tr>'."\n\n";
			$rowNum++;
		}

		// page navigation for archives
		$entries = getOne("SELECT COUNT(*) FROM users");
		if ( $entries > $limitNum ) {
			$totalPages = (int) ceil($entries / $limitNum);
			if ( $page ) { $pageNav = pageNumberNav($totalPages, $page); }
			else { $pageNav = pageNumberNav($totalPages, 1); }

			echo '<tr><td colspan="5">'.$pageNav.'</td></tr>';
		}
	} else {
		echo '<tr><td>There are no users. How are you here?</td></tr>'."\n";
	}
?>
</table><br />
<hr /><br />
<input type="submit" value="delete" class="button" />
</form>
<?php
}


function deleteUser () {
	$uid = getOne("SELECT uid FROM users WHERE userName='".$_POST['user']."'");
	$query = "DELETE FROM users WHERE uid='".$uid."'";
	$query2 = "DELETE FROM user_permissions WHERE uid='".$uid."'";

	// run query
	if ( commandQuery($query) && commandQuery($query2) ) {	// successful
		// display deletion confirmation message
		$displayMessage = '<p class="confirmation">User <strong>'.$_POST['user'].'</strong> successfully deleted.</p>'."\n";

		make_htpasswd();	// update .htpasswd files
/*		if ( $updated_htpass ) {	// update successful
			$displayMessage .= '<p class="confirmation">.htpasswd file updated successfully.</p>'."\n";
		} else {	// update failed
			$displayMessage .= '<p class="error">.htpasswd file could not be updated.</p>'."\n";
		}
*/
		$_GET['step'] = "ready";	// change mode of page so it won't try to process again
	} else {	// query not successful, show error message
		$displayMessage = '<p class="error">Error: Could not delete user <strong>'.$_POST['user'].'</strong>.</p>'."\n";
	}

	return $displayMessage;
}

?>