<?php

session_start();

if ( !$_SESSION['validUser'] ) { header("Location: .."); exit; }

require_once('dbFunctions.php');
require_once('DB.php');

//
// getPermissionLevels()
// Gets a list of the possible permission settings defined in the database
// The list is defined as a constant and can be used throughout the system
// 
function getPermissionLevels () {
	$query = "SELECT permissionID, permissionName FROM permissions";
	$res = returnQuery($query);

	while ( $row =& $res->fetchRow() ) {
		define( strtoupper($row[1]), $row[0] );
		$permissionLevels[] = strtoupper($row[1]);
	}
	$res->free();

	define ( 'PERMISSION_LEVELS', serialize($permissionLevels) );
}



//
// getUserPermissions()
// Gets the permissions granted to a particular user, collects the list in an array,
// and writes the array to the session variable.
//
function getUserPermissions ( $validUser ) {
	$query = "SELECT permissionName FROM permissions, user_permissions, users "
			. "WHERE userName='".$validUser."' AND permissions.permissionID=user_permissions.permissionID "
			. "AND users.uid=user_permissions.uid";
	$res = returnQuery($query);

	while ( $row =& $res->fetchRow() ) {
		$permissions[] = strtoupper($row[0]);
	}

	$_SESSION['permissions'] = $permissions;
//	return($permissions);
}




/*
define( 'ADMIN', 1 );	// can manage other users
define( 'DATABASE', 2 );	// can access database
define( 'EMAIL_CONTENT', 4);	// send mass emails and edit content
define( 'EMAIL', 6 );	// can send mass emails for the site
define( 'CONTENT', 8 );	// can edit site content
define( 'HTPASSWD', 12 );	// has access to protected dirs
define( 'PODCASTADMIN', 15 );	// admin for podcast
define( 'PODCAST', 16 );	// submitter of podcast
*/
/*
function authorized ( $clearance_needed = null ) {
//	global $_SESSION;
	if ( !$_SESSION['validUser'] ) {
		return false;	// user is not logged in
	}

	if ( $clearance_needed === null ) {
		return true;	// the user merely needs to be logged in
	} elseif ( $clearance_needed < $_SESSION['clearance'] ) {
		return false;	// the user lacks proper clearance
	} else {
		return true;	// the user has sufficient clearance
	}

	return false;	// just in case there's a logic hole somewhere
}

if ( authorized() ) {
	// any logged in user would see this
}

if ( authorized(ADMIN) ) {
	//only admins can see this stuff
}
*/
?>