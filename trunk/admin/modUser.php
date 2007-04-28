<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('ADMINISTRATOR', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('DB.php');
require_once('adminConstructFunctions.php');
require_once('dbFunctions.php');
require_once('htpasswdManager.php');
require_once('../pageComponents/formTools.php');

$pageTitle = "change user permissions";
$pageName = "modUser";

printAdminHead($pageTitle, $pageName);
?>
<script language="JavaScript">
function changeGETvar(varToChange, newValue) {
  var GETvars = window.location.search.substring(1);	// GET string from url
  var newGETvars = '?';	// new GET string (starts with '?')
  var vars = GETvars.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if (pair[0] == varToChange) {
      pair[1] = newValue;
    }
	// put the GET string back together (with updated value)
	newGETvars = newGETvars + pair[0] + '=' + pair[1] + '&';
  }
  newGETvars = newGETvars.substring(0, newGETvars.length - 1);	// trim the ending '&'
  var stop = window.location.href.length - window.location.search.length;	// length of url except for GET string
  var baseLocation = window.location.href.substring(0, stop);	// url except for GET string
  window.location = baseLocation+newGETvars;	// reload the page with the updated GET string
}
</script>

<h1>Change a user's permission settings</h1>

<p>Select the user you wish to modify and click the <em>Next</em> button.  On the second page 
select the permissions you want that user to have.  Click the <em>Update</em> button 
to submit the changes.</p>

<p><a href="manageUsers.php">Cancel</a></p>

<?php

	// select the appropriate action
	if ( $_GET['step'] == 'process' ) {
		if ( $_POST['user'] ) {
			$result = modifyUser(); 
			echo $result;
			showUsers();
		} else {
			echo '<p class="error">Error: You must select a user to modify.</p>';
		}
	} elseif ( $_GET['step'] == 'modify' ) {
		if ( $_POST['user'] ) {
			showUserPermissions($_POST['user']);
		} else {
			echo '<p class="error">Error: You must select a user to modify.</p>';
			showUsers();
		}
	} elseif ( $_GET['step'] == 'users' ) {
		showUsers();
	} else {
		searchForm();
	}
?>


<p><a href="manageUsers.php">Cancel</a></p>

<?php
printAdminBottom($pageName);


//functions

function searchForm () {
?>
<div class="divBox">
<h2>Search users:</h2><br />

<p>
Enter criteria to narrow the user listing. <em>Name</em> searches both first and last names. 
Both <em>name</em> and <em>username</em> can search partial matches (i.e. searching "ca" in 
<em>name</em> would match "<strong>Ca</strong>rol," "Jessi<strong>ca</strong>," 
"Dun<strong>ca</strong>n," etc.). These fields are NOT case-sensitive. 

</p>

<form method="get" action="modUser.php?step=users">
<input type="hidden" name="step" value="users" />

<table>
<tr>
	<td>Name:</td>
	<td><input type="text" name="n" size="25" maxlength="50" /></td>
</tr>
<tr>
	<td>Username:</td>
	<td><input type="text" name="u" size="20" maxlength="25" /></td>
</tr>
<tr>
	<td>Permissions:</td>
	<td>
	<select name="p" style="font-size:.8em;">
		<option value="" selected="selected">*** Any ***</option>
<?php
	$query = "SELECT permissionID, permissionName FROM permissions";

	$res = returnQuery($query);

	while ( $row =& $res->fetchRow() ) {
		echo "\t\t".'<option value="'.$row[0].'">'.strtoupper($row[1]).'</option>'."\n";
	}
	$res->free();
?>
	</select>
	</td>
</tr>
<tr>
	<td>Account Status:</td>
	<td>
	<select name="s" style="font-size:.8em;">
		<option value="" selected="selected">*** Any ***</option>
		<option value="active">active</option>
		<option value="pending">pending</option>
	</select>
	</td>
</tr>
</table>

<p>
<input type="hidden" name="limit" value="20" />
<input type="submit" value="search" class="button" />
</p>

</form>
</div>
<?php
}


function runSearch () {

}


function showUsers () {
	// set default for LIMIT clause in query
	if ($_GET['limit'] && onlyDigits($_GET['limit']) && $_GET['limit']<=50 && $_GET['limit']>0) {
		$limitNum = $_GET['limit'];
	} else {
		$limitNum = 20;
	}
	$limit = ' LIMIT ' . $limitNum;	// number of results for a page

	// check for page (show older posts)
	if ( onlyDigits($_GET['page']) && $_GET['page'] != null ) {
		$page = $_GET['page'];
		if ( $page > 1 ) {	// page 2 or above
			$skip = ($page-1) * $limitNum;
			$limit = ' LIMIT ' . $skip . ',' . $limitNum;	// skip results from previous pages and get this page's results
		}
	}


	// check user search values and create related query clauses
	if ( $_GET['n'] && isLetters($_GET['n']) ) { $name = "(users.fname LIKE '%".$_GET['n']."%' OR users.lname LIKE '%".$_GET['n']."%') "; } else { $name = ''; }
	if ( $_GET['u'] && isAlphaNumeric($_GET['u']) ) { $username = "users.userName LIKE '%".$_GET['u']."%' "; } else { $username = ''; }
	if ( $_GET['p'] && onlyDigits($_GET['p'] ) ) { $permissions = "permissionID='".$_GET['p']."' "; } else { $permissions = ''; }
	if ( $_GET['s'] && ($_GET['s']=='active' || $_GET['s']=='pending') ) { $status = "accountStatus='".$_GET['s']."' "; } else { $status = ''; }


	// build the where clause
	if ( $name || $username || $permissions || $status ) {
		// add query clauses to array
		if ( $name ) $wherePieces[] = $name;
		if ( $username ) $wherePieces[] = $username;
		if ( $permissions ) $wherePieces[] = $permissions;
		if ( $status ) $wherePieces[] = $status;

		// construct full where clause
		$whereClause = "WHERE ";
		$whereClause .= implode("AND ", $wherePieces);
	} else { $whereClause = ''; }


	// build the from clause
	if ( $permissions ) {	// if searching by permissions, include user_permission table and join on uid
		$fromClause = "FROM users LEFT JOIN user_permissions ON users.uid=user_permissions.uid ";
	} else {
		$fromClause = "FROM users ";
	}


	// count the total matches for this search (used for pagination)
	$totalMatches = getOne("SELECT COUNT(*) ".$fromClause.$whereClause);


	// eliminate possible conflicts between items per page and page navigation
	if ( $totalMatches < $limitNum ) $limit = '';	// all matches will fit on one page
	if ( $skip+1 > $totalMatches ) {	// if you're on a page beyond the reach of the items per page you change to
		$lastPage = (int) ceil($skip / $limitNum);
?>
<script language="JavaScript">
changeGETvar('page',<?php echo $lastPage; ?>);
</script>
<?php
	}


	// build query
	$userQuery = "SELECT users.uid, userName, fname, lname, accountStatus "
			. $fromClause
			. $whereClause
			. "ORDER BY lname, fname".$limit;

	$userData = returnQuery($userQuery);
	$rowNum = 1;
?>
<div class="divBox">
<h2>Current users:</h2><br />

Records per page: 
<select name="limit" onchange="changeGETvar('limit',this.value)">
<option value=""></option>
<option value="5"<?php if ($_GET['limit']==5) echo ' selected="selected"'; ?>>5</option>
<option value="10"<?php if ($_GET['limit']==10) echo ' selected="selected"'; ?>>10</option>
<option value="20"<?php if ($_GET['limit']==20) echo ' selected="selected"'; ?>>20</option>
<option value="40"<?php if ($_GET['limit']==40) echo ' selected="selected"'; ?>>40</option>
</select><br /><br />

<form method="post" action="modUser.php?step=modify">
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

		// gather all of the pieces of the $_GET variable for use in page links
		foreach ( $_GET as $key => $value ) {
			if ( $key != 'page' ) $url .= $key.'='.$value.'&';
		}

		// page navigation for archives
		if ( $totalMatches > $limitNum ) {
			$totalPages = (int) ceil($totalMatches / $limitNum);
			if ( $page ) { $pageNav = pageNumberNav($totalPages, $page, $url); }
			else { $pageNav = pageNumberNav($totalPages, 1, $url); }

			echo '<tr><td colspan="5"><br />'.$pageNav.'</td></tr>';
		}
	} else {
		echo '<tr><td>There are no users. How are you here?</td></tr>'."\n";
	}
?>
</table><br />

<hr />

<p>
<input type="submit" value="Next" class="button" />
</p>
</form>
</div>
<?php
}


function showUserPermissions($user) {
?>
<div class="divBox">
<h2>User permissions:</h2><br />

<p>
Permission update for user: <strong><?php echo $user; ?></strong><br />
Check the boxes exactly corresponding to the permissions this user should have, and 
uncheck any boxes representing permissions this user is not supposed to have. 
Then click the <em>Update</em> button below.
</p>

<form method="post" action="modUser.php?step=process">
<?php
	// get this user's current permissions
	$query = "SELECT permissions.permissionID FROM permissions, users, user_permissions "
			. "WHERE userName='".$user."' AND users.uid=user_permissions.uid AND user_permissions.permissionID=permissions.permissionID";

	$res = returnQuery($query);

	while ( $row =& $res->fetchRow() ) {
		$userPermissions[] = $row[0];
	}
	$res->free();

	$permissionsArray = showPermissions();	// array of all available permissions
	if ( $permissionsArray ) {
		echo "\t".'<ul class="selectionList">'."\n";
		for ($i=0; $i<count($permissionsArray); $i++) {
			echo "\t".'<li><input type="checkbox" name="permissions[]" value="'.$permissionsArray[$i][0].'" ';

			// check the appropriate boxes
			if ( in_array($permissionsArray[$i][0], $userPermissions) ) {
				echo 'checked="checked" ';
			}

			echo '/> <strong>'.$permissionsArray[$i][1].'</strong>: '.$permissionsArray[$i][2]."</li>\n";
		}
		echo "\t</ul>\n";
	}

	// Account status active/pending selection
	$accountStatus = getOne("SELECT accountStatus FROM users WHERE userName='".$user."'");
?>
<br />

<h2>Account status:</h2>
<p>
<input type="radio" name="status" value="active" <?php if ($accountStatus == "active") echo 'checked="checked "'; ?>/> <span class="confirmation"><strong>active</strong></span> &nbsp;
<input type="radio" name="status" value="pending" <?php if ($accountStatus == "pending") echo 'checked="checked "'; ?>/> <span class="error"><strong>pending</strong></span>
</p>
<hr />

<input type="hidden" name="user" value="<?php echo $user; ?>" />
<p><input type="submit" value="Update" class="button" /></p>
</form>
</div>
<?php
}


function modifyUser () {
	$uid = getOne("SELECT uid FROM users WHERE userName='".$_POST['user']."'");

	// clear entries for this user from user_permissions table
	$deleteQuery = "DELETE FROM user_permissions WHERE uid='".$uid."'";
	commandQuery($deleteQuery);

	// insert current entries into user_permissions
	$success = true;
	foreach ($_POST['permissions'] as $permission) {
		$insertQuery = "INSERT INTO user_permissions SET uid='$uid', permissionID='$permission'";
		if ( !commandQuery($insertQuery) ) { $success = false; }
	}
	if ( $success === false ) {
		$displayMessage .= '<p class="error">There was an error while trying to update the database</p>'."\n";
	} else {
		$displayMessage .= '<p class="confirmation">Database successfully updated</p>'."\n";
	}

	// update .htpasswd files
	if ( make_htpasswd() ) {
		$displayMessage .= '<p class="confirmation">.htpasswd files successfully updated</p>'."\n";
	} else {
		$displayMessage .= '<p class="error">There was an error while trying to update the .htpasswd files</p>'."\n";
	}

	// account status
	if ( !commandQuery("UPDATE users SET accountStatus='".$_POST['status']."' WHERE uid='$uid'") ) {
		$displayMessage .= '<p class="error">User status could not be updated</p>'."\n";
	} else {
		$displayMessage .= '<p class="confirmation">User status successfully updated</p>'."\n";
	}

	$_GET['step'] = '';
	return $displayMessage;
}

?>