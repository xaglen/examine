<?php
/**
 * This file should be included at the top of any page needing restricted access
 * It both provides the classes necessary for authentication and initializes the
 * authentication object.
 *
 * @package examine
 * @subpackage security
 */

require_once 'Auth.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/Tableless.php';
require_once basename(__FILE__).'/../config.php';

/**
 * Extended PEAR Auth class to allow easier internal data checks with out schema
 *
 * @author Glen Davis
 * @package examine
 */
class myAuth extends Auth {

private $pid = null;

/**
   * what is the pid of the logged in person?
   *
   * @return int
   * @author Glen Davis
   */
    function getPid() {
		if ($this->pid===null) {
			$db=createDB();
			$this->pid=$db->getOne('SELECT pid FROM users WHERE username='.$db->quote($this->getUsername()));
			$db->disconnect();
		} 
		return $this->pid;
    }


    /**
     * does the logged-in user have rights to edit an event?
     *
     * @param int $event_id primary key to table events
     * @return boolean
     * @author Glen Davis
     */
    function ownsEvent($event_id=NULL) {
        return 1;
    }
    

    /**
     * does the logged-in user have rights to edit a person?
     *
     * @param int $pid primary key to table people
     * @return boolean
     * @author Glen Davis
     */
    function ownsPerson($pid=NULL) {
        return 1;
    }

    /**
     * does the logged-in user have rights to edit a subgroup?
     *
     * @param int $subgroup_id primary key to table subgroups
     * @return boolean
     * @author Glen Davis
     */
    function ownsGroup($subgroup_id=NULL) {
        return 1;
    }

    /**
     * does the logged-in user have rights to edit a ministry?
     *
     * @param int $ministry_id primary key to table ministries
     * @return boolean
     * @author Glen Davis
     */
    function ownsMinistry($ministry_id=NULL) {
        return 1;
    }
}

/**
 * Displays a login form
 *
 * @param string $username the previously attempted username
 * @param int $status the status code from Auth - one of a set of predefined constants
 * @param object &$auth the Auth object itself
 * @author Glen Davis
 */
function loginForm($username='',$status=null,&$auth=null) {
global $_SERVER;
global $dbUser;
global $dbHost;
global $dbPass;
global $dbName;

    $form = new HTML_QuickForm('login', 'POST',$_SERVER['PHP_SELF'],null,null,true);
	$form->addElement('header','','Login');
    $form->addElement('text', 'username', 'Email:');
	$form->addRule('username','both username and password are required','required',null,'client');
    $form->addElement('password', 'password', 'Password:');
	$form->addRule('password','both username and password are required','required',null,'client');
	if (isset($status)) {
		switch ($status) {
		case AUTH_IDLED: 
			$statusMsg='Session Timed Out';
			$form->setElementError('username','session timed out');
			break;
		case AUTH_EXPIRED: 
			$statusMsg='Session Expired';
			$form->setElementError('username','session expired');
			break;
		case AUTH_WRONG_LOGIN:
			$statusMsg='Invalid Username or Password';
			$form->setElementError('password','invalid username or password');
			$form->setElementError('username','invalid username or password');
			break;
		case AUTH_SECURITY_BREACH:
			die(); // big time error
		default:
			$statusMsg='Please Login';
		}
	 }
	
    $form->addElement('submit', 'submit', 'Log In!');
	$form->setRequiredNote(' ');
	$form->setJsWarnings(' ',' ');
	$form->addElement('link','forgot','','http://chialpha.com/login/wrt/examine/forgot_password.php','forgot your password?');
	$renderer =& new HTML_QuickForm_Renderer_Tableless();
	$form->accept($renderer);
	?>
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo $statusMsg;?></title>
<link rel="stylesheet" type="text/css" title="default" href="examine.css"/>
<link rel="stylesheet" type="text/css" title="default" href="quickform.css"/>
</head>
<body>
	<?
	echo $renderer->toHtml();
	?>
	<p>Welcome to eXAmine - Chi Alpha's web-based administration tool.</p>
    <p>You can give me a test-drive by logging in with username <b>example@chialpha.com</b> and password <b>demo</b> (don't worry - you can't mess anything up, the example database is repopulated every hour).</p>
    <p>We are currently storing information for
    <?php
    $db = new mysqli($dbHost,$dbUser,$dbPass,$dbName);
    $result=$db->query('SELECT COUNT(*) FROM ministries');
    $row=$result->fetch_row();
    $ministries=$row[0];
    $result->close();
    $result=$db->query('SELECT COUNT(*) FROM people');
    $row=$result->fetch_row();
    $people=$row[0];
    $result->close();
    echo "$ministries ministries encompassing $people people. Add yours!</p>";
    ?>
    </body>
	</html>
	<?php
}

/**
 * Does a user have rights to edit an event?
 *
 * @param int $pid primary key to table people
 * @param int $event_id primary key to table events
 * @return boolean
 */
function ownsEvent($pid=null,$event_id=null) {
    return true;
}

/**
 * Does a user have rights to edit a  ministry?
 *
 * @param int $pid primary key to table people
 * @param int $ministry_id primary key to table ministries
 * @return boolean
 */
function ownsMinistry ($pid=null, $ministry_id=null) {
    return true;
}

$a = &new myAuth("MDB2", $loginOptions,'loginForm');
$a->setSessionname('chi_alpha_examine');
$a->setIdle(900); // fifteen minutes
$a->start();
 
if (!$a->getAuth()) {
	  exit();
}

if (isset($_GET['logout'])) {
      $a->logout();
      header("Location: http://chialpha.com/login/wrt/examine/");
	  exit();
}
?>