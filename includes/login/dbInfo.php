<?php

// information used to connect to the database
$dsn = array(
    'phptype'  => 'mysql',
    'hostspec' => 'localhost',
    'port'     => '3306',
    'database' => 'natlXA',
    'username' => ''
);

define ( DSN , serialize($dsn) );

/*
$dsn = array(
    'phptype'  => 'mysql',
    'hostspec' => 'localhost',
    'port'     => '3306',
    'database' => '',
    'username' => '',
    'password' => ''
);
*/

?>
