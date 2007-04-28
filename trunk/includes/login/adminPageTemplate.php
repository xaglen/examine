<?php

session_start();

if ( !$_SESSION['validUser'] ) { header("Location: .."); exit; }

include_once('dataCheckFunctions.php');
include_once('dbInfo.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');

$pageTitle = "";
$pageName = "";

printAdminHead($pageTitle, $pageName);
?>

<h1></h1>

<p></p>

<?php
printAdminBottom($pageName);

//functions


?>