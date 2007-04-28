<?php
error_reporting(E_ALL | E_NOTICE);
require_once 'MDB2.php';
require_once 'includes/debug.php';

$mdb2_user="";
$mdb2_pass="";
$mdb2_host="localhost";
$mdb2_db_name="";

$dsn = 'mysql://'.$mdb2_user.':'.$mdb2_pass.'@'.$mdb2_host.'/'.$mdb2_db_name;
?>
