<?php
/**
 * this file handles searches for users
 *
 * @package examine
 * @subpackage interface
 */
require_once 'config.php';
require_once 'functions.php';

$db=createDB();

if (!isset($_REQUEST['s'])) {
	die ("No search term");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>
Search Results
</title>
<link rel="stylesheet" href="examine.css" type="text/css" />
</head>
<body>
<div id="main">
<div id="sidebar">
&nbsp;
</div>
<?php
if (!get_magic_quotes_gpc()) {
	$s = addslashes($_GET['s']);
} else {
	$s = $_GET['s'];
}
echo "<H1>Search Results For '$s'</H1>";
$sql="SELECT people_id,preferred_name,last_name,MATCH(preferred_name,middle_name,lastname) AGAINST ('$s') as score FROM people WHERE MATCH(preferred_name,middle_name,last_name) AGAINST ('$s') ORDER BY score DESC";
$result=$db->query($sql);
echo '<ul>';
while ($row=$result->fetchRow()) {
	printf('<li><a href="people.php?id=%s">%s %s</a></li>',$row['people_id'],$row['first_name'],$row['last_name']);
}
echo '</ul>';
?>
</div>
</body>
</html>
