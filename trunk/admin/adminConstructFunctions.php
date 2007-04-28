<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

session_start();

include_once('../pageComponents/definitions.php');


// display html code for top of a page
function printAdminHead ( $pageTitle, $pageName ) {
	if ( $pageName == 'galleryAdmin' ) require_once('../../pageComponents/definitions.php');
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo $pageTitle; ?></title>
<link href="<?php echo ADMIN_STYLE; ?>" rel="stylesheet" type="text/css" />
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
</script>
</head>
<body onLoad="placeFocus()">

<? adminMenu($pageName); ?>


<table id="mainTable">
<tr><td>
<?php
}


function adminMenu ( $pageName ) {
?>
<table class="menu">
<tr>
	<td><?php if ( $pageName != 'menu' ) echo '<a href="'.ADMIN.'menu.php?display=menu">main&nbsp;menu</a>'; else echo '<span class="current">main&nbsp;menu</span>'; ?></td>
	<td>|</td>
	<td><?php if ( $pageName != 'manageContent' && in_array('CONTENT', $_SESSION['permissions']) ) echo '<a href="'.ADMIN.'manageContent.php">site&nbsp;content</a>'; else echo '<span class="current">site&nbsp;content</span>'; ?></td>
	<td>|</td>
	<td><?php if ( $pageName != 'email' && in_array('EMAIL', $_SESSION['permissions']) ) echo '<a href="'.ADMIN.'email_plain.php">email</a>'; else echo '<span class="current">email</span>'; ?></td>
	<td>|</td>
	<td><?php if ( $pageName != 'manageUsers' && in_array('ADMINISTRATOR', $_SESSION['permissions']) ) echo '<a href="'.ADMIN.'manageUsers.php">users</a>'; else echo '<span class="current">users</span>'; ?></td>
	<td>|</td>
	<td><?php if ( $pageName != 'manageDB' && in_array('DATABASE', $_SESSION['permissions']) ) echo '<a href="'.ADMIN.'manageDB.php">database</a>'; else echo '<span class="current">database</span>'; ?></td>
	<td>|</td>
	<td><a href="<?php echo ADMIN; ?>logout.php" class="logout">logout</a></td>
</tr>
</table>
<?php
}


function printAdminBottom ( $pageName ) {
?>

</td></tr>
</table>

<? adminMenu($pageName); ?>

</body>
</html>

<?php
}
?>