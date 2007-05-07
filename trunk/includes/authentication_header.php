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
 * Extended PEAR Auth class to allow easier internal data checks with our schema
 *
 * @author Glen Davis
 * @package examine
 */
class myAuth extends Auth {

private $tokenCookieName = 'examinetoken';
private $pid = null;
private $user_id = null;
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
     *  what is the user_id of the logged in person?
     * 
     * @return int
     * @author Glen Davis
     */
    function getUserId() {
        if ($this->user_id===null) {
            $db=createDB();
            $this->user_id=$db->getOne('SELECT user_id FROM users WHERE username='.$db->quote($this->getUsername()));
            $db->disconnect();
        }
        return $this->user_id;
    }              

	/**
	* overwrite parent function because as it stands it won't allow me to do the session checks that I need to for the token-based "remember me" scheme
	 * See http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice
	 * @return boolean is the user logged in OR has the user checked remember me?
	*/
	function checkAuth() {
		//only deal with tokens on first login attempt
		if ($this->authChecks>0) {
			return parent::checkAuth();
		}
		$this->authChecks++; // critical - without this the parent function is never called
		global $_COOKIE;
		if (!isset[$_COOKIE[$this->tokenCookieName]) {
			return false;
		} else {
			$cookie=$_COOKIE[$this->tokenCookieName];
			$user_id=$cookie['user_id'];
			$token=$cookie['token'];
		}
		$db=createDB();
		// maintenance - delete all entries older than one month - probability 5%
		if (mt_rand(1,20)==20) {
			$db->exec('DELETE FROM user_remember_me WHERE TIMESTAMPDIFF(DAYS,NOW(),created_on)>31');
		}
		$result=$db->query("SELECT token,username FROM user_remember_me urm,users u WHERE urm.user_id=$user_id AND u.user_id=urm.user_id");
		// there can be many entries in the database if the user uses multiple computers
		while ($row=result->fetchRow()) { 
			if ($token==$row['token']) {
				$this->updateToken();
				$this->username=$row['username'];
				return true;
			}
		}
		return false;
	}
    
/**
 * key part of "remember me" functionality. See http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice
 */
    function updateToken() {
        $db=createDB();
        $token=mt_rand();
        $user_id=$this->getUserId();
        $db->exec("INSERT INTO user_remember_me (user_id, token) VALUES ($user_id, $token)");
		setcookie($this->tokenCookieName.'[token]',$token,time()+60*60*24*30);
		$db->disconnect();
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
    $form->addElement('checkbox','remember', 'Remember me?');
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

function successfulLogin($username=null,$a=null) {
	global $_POST;
	if (isset($_POST['remember'])) {
		$a->updateToken();
	}
}

$a = &new myAuth("MDB2", $loginOptions,'loginForm');
$a->setSessionname('chi_alpha_examine');
//$a->setIdle(900); // fifteen minutes
$a->setLoginCallback('successfulLogin');
//$a->setCheckAuthCallback('checkAuthToken');
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
