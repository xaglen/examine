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

printAdminHead($pageTitle, $pageName);

?>

<h1>Add an email address to the mailing list</h1>

<p>Enter the email address and person's name, and then click 
the <em>add email</em> button at the bottom of the page.</p>

<p><a href="menu.php?display=menu">Cancel</a></p>

<hr /><br /><br />

<form name="emailList" action="../pageComponents/manageEmailList.php?action=add" method="post">
<table>
<tr>
<td>first name: </td>
<td></td>
<td><input type="text" name="fname" /></td>
</tr>
<tr>
<td>last name: </td>
<td></td>
<td><input type="text" name="lname" /></td>

</tr>
<tr>
<td>email address: </td>
<td></td>
<td><input type="text" name="email" /></td>
</tr>
<tr>
<td colspan="2"></td>
<td><input class="button" type="submit" value="add email" /></td>
</tr>
</table>
</form>

<br />
<hr />

<p><a href="menu.php?display=menu">Cancel</a></p><br />

<?php

printAdminBottom($pageName);

?>