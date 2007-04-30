<?php
require_once basename(__FILE__).'/../config.php';
require_once 'Auth.php';

$a = &new Auth("MDB2", $loginOptions);
$exampleUser = 'example@chialpha.com';
$examplePass = 'demo';
$users = $a->listUsers();

// if the user already exists then reset the password, otherwise create
$userExists=false;
foreach($users as $user) {
    if ($user['username']==$exampleUser) {
        $userExists=true;
        break;
    }
}
if ($userExists) {
    $a->changePassword($exampleUser,$examplePass);
} else {
    $a->addUser($exampleUser, $examplePass);
}
?>
