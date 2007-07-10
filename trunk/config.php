<?php
error_reporting(E_ALL | E_NOTICE);
require_once 'MDB2.php';
require_once 'includes/debug.php';

$rootdir = dirname(__FILE__);
$rooturl = 'http://chialpha.com/login/wrt/examine';

// You should not need to change anything below this line

include($rootdir.'/config.db.php');

$loginOptions = array(
        'dsn' => $dsn,
        'table' => 'users',
        'advancedsecurity' => true,
        'sessionName' => 'chi_alpha_examine'
        );
?>
