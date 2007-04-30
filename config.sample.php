<?php
error_reporting(E_ALL | E_NOTICE);
require_once 'MDB2.php';
require_once 'includes/debug.php';

$dbUser="";
$dbPass="";
$dbHost="localhost";
$dbName=""; // the actual name of the database

$dsn = 'mysqli://'.$dbUser.':'.$dbPass.'@'.$dbHost.'/'.$dbName;

// You should not need to change anything below this line

$loginOptions = array(
        'dsn' => $dsn,
        'table' => 'users',
        'advancedsecurity' => true,
        'sessionName' => 'chi_alpha_examine'
        ); 
?>
