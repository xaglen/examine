<?php

session_start();

require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

tableToCSV($dsn,"emaillist","testFile.csv");

?>