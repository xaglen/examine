<?php
require_once dirname(__FILE__).'/../config.php';
require_once dirname(__FILE__).'/../includes/functions.php';
require_once 'Auth.php';
require_once 'MDB2.php';

//$db=createDB();

//$types=array('integer','text','text','integer','text');
//$db->prepare('REPLACE INTO TABLE people (people_id,first_name,last_name,category_id,gender) VALUES (?,?,?,?,?)',$types, MDB2_PREPARE_MANIP);
?>
<HTML>
<HEAD>
<TITLE>Refresh Sample Data</TITLE>
</HEAD>
<BODY>
<?php

$db = createDB();

$sql = "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.ministries.csv' REPLACE INTO TABLE ministries FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

$sql = "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.people.csv' REPLACE INTO TABLE people FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'"; 
echo $sql.'<br/>';
$db->query($sql);

$sql = "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.schools.csv' REPLACE INTO TABLE schools FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

$sql =  "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.ministry.people.csv' REPLACE INTO TABLE ministry_people FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

$sql =  "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.events.csv' REPLACE INTO TABLE events FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

$sql =  "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.event.attendance.csv' REPLACE INTO TABLE event_attendance FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

$sql = "LOAD DATA LOW_PRIORITY LOCAL INFILE 'data/sample.users.csv' REPLACE INTO TABLE users FIELDS TERMINATED BY ';' OPTIONALLY ENCLOSED BY '\"'";
echo $sql.'<br/>';
$db->query($sql);

// set up example login user - now defunct. Keep for reference.
/*
$a = &new Auth("MDB2", $loginOptions);
$exampleUser = 'example@chialpha.com";
$examplePass = 'demo";
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
*/
?>
</BODY>
</HTML>
