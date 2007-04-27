<?php
error_reporting(E_ALL | E_STRICT | E_NOTICE);
require_once 'MDB2.php';
require_once 'includes/debug.php';

$mdb2_user="";
$mdb2_pass="";
$mdb2_host="localhost";
$mdb2_db_name="";
$dsn = 'mysql://.'$mdb2_user.':'.$mdb2_pass.'@'.$mdb2_host.'/'.$mdb2_db_name;

//to use this function simply call $db=createDB(); 
function createDB() {
global $dsn;

$db =& MDB2::factory($dsn);
$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
$db->loadModule('Extended');
$db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);
return $db;
}
?>