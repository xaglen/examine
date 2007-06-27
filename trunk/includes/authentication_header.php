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
require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
require_once 'HTML/QuickForm/Renderer/Tableless.php';
require_once basename(__FILE__).'/../config.php';
require_once $rootdir.'/includes/functions.php';

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
	private $token = null;
	
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
		//only deal with tokens on first login attempt - otherwise call the parent function
		if ($this->getAuthData('checkedToken')) {
			$this->log('token already checked - calling parent auth function');
			return parent::checkAuth();
		}
		$this->setAuthData('checkedToken',true); // critical - without this the parent function is never called
		$this->log('checking token');
        global $_COOKIE;
		if (!array_key_exists($this->tokenCookieName,$_COOKIE)) {
			$this->log('cookie not set - initial auth failed');
			return parent::checkAuth();
		}
        $user_id=$_COOKIE[$this->tokenCookieName]['user_id'];
        $token=$_COOKIE[$this->tokenCookieName]['token'];

        if (!$user_id || !$token) {
			$this->log('user_id or token is null - initial auth failed');
            return parent::checkAuth();
        }

		$db=createDB();
		// maintenance - delete all entries older than one month - probability 5%
		if (mt_rand(1,20)==20) {
			$db->exec('DELETE FROM user_remember_me WHERE TIMESTAMPDIFF(DAY,NOW(),created_on)>31');
		}
		$result=$db->query("SELECT token,username FROM user_remember_me urm,users u WHERE urm.user_id=$user_id AND u.user_id=urm.user_id");
		// there can be many entries in the database if the user uses multiple computers
		while ($row=$result->fetchRow()) { 
			$this->log('checking tokens...');
			if ($token==$row['token']) {
				$this->log('token found - should log in now');
                $this->token=$token; // necessary for updateToken to work properly
				$this->updateToken();
				$this->username=$row['username'];
                $this->setAuthData('usedToken',true);
                return true; // the return true is what actually logs them on
			}
		}
		return parent::checkAuth(); // used to return false - but that led to blank screens
	}
	
	/**
	* key part of "remember me" functionality. See http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice
    * @TODO this is glitchy
	*/
	function updateToken() {
		$db=createDB();
		$token=mt_rand();
		$user_id=$this->getUserId();
		$this->log('updating token for user_id: '.$user_id);
        if (!$user_id) {
            return;
        }
		$db->exec("INSERT INTO user_remember_me (user_id, token) VALUES ($user_id, $token)");
		setcookie($this->tokenCookieName.'[token]',$token,time()+60*60*24*30);
        setcookie($this->tokenCookieName.'[user_id]',$user_id,time()+60*60*24*30);
        // delete currently used token (if it exists)
        if ($this->token) {
            $db->exec('DELETE FROM user_remember_me WHERE user_id='.$user_id.' AND token='.$this->token);
        }
        $this->token=$token;
		$db->disconnect();
	}

/**
 * destroy all tokens associated with the currently logged-in user
 */
    function nukeTokens() {
        $db=createDB();
        $user_id=$this->getUserID();
        $db->exec("DELETE FROM user_remember_me WHERE user_id=$user_id");
        $this->token=null;
        $db->disconnect();
    }

/**
 * extract the username from the token cookie
 * part of the remember-me functionality
 * @return string the name of the user
 */
    function getTokenUsername() {
           global $_COOKIE;
           if (!array_key_exists($this->tokenCookieName,$_COOKIE)) {
               return '';
           }
           $user_id=$_COOKIE[$this->tokenCookieName]['user_id'];
           if (!$user_id) {
               return '';
           }
           $db=createDB();
           $username=$db->getOne('SELECT username FROM users WHERE user_id='.$user_id);
            return $username;
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
 * Sets a user preference
 *
 * @param string $prefname the preference to be set
 * @param string $prefval what the preference is - a serialized PHP variable. It is the responsibility of the calling function to serialize the data.
 */
function setPreference($prefname=null,$prefval=null) {
	if ($prefname===null || $prefval===null) {
		return;
	}
	$db=createDB();
	$user_id=$this->getUserId();
	$prefname=$db->quote($prefname);
	$prefval=$db->quote($prefval);
	$sql="INSERT INTO user_preferences (user_id,prefname,prefval) VALUES ($user_id,$prefname,$prefval) ON DUPLICATE KEY UPDATE prefval=VALUES(prefval)";
	$db->exec($sql);
}

/**
 * Retrieves a user preference
 * 
 * @param string $prefname the preference to retrieve
 * @return string a serialized PHP variable. Call unserialize on this returned value.
 */
function getPreference($prefname=null) {
if ($prefname===null) {
		return null;
	}
	
	$db=createDB();
	$prefname=$db->quote($prefname);
	$user_id=$this->getUserId();
	$sql="SELECT prefval FROM user_preferences WHERE user_id=$user_id AND prefname=$prefname";
	$prefval=$db->getOne($sql); //user_pid and prefname are the key together, so there will never be two entries
	return $prefval;
}
	
} // END OF CLASS

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

		$form = new HTML_QuickForm_DHTMLRulesTableless('login', 'POST',$_SERVER['PHP_SELF'],null,null,true);
		$form->addElement('header','','');
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
		
		//$form->addElement('submit', 'submit', 'Log In!');
		$group[]=&HTML_QuickForm::createElement('xbutton', 'btnSave', '<img src="graphics/icons/tick.png" height="16" width="16"/> Login', array('class'=>'positive','onclick'=>'this.submit()'));
		$form->getValidationScript();
		$form->addGroup($group, null, '', ' ');
		$form->setRequiredNote(' ');
		$form->setJsWarnings(' ',' ');
		$form->addElement('link','forgot','','http://chialpha.com/login/wrt/examine/forgot_password.php','forgot your password?');
		$renderer =& new HTML_QuickForm_Renderer_Tableless();
		$form->accept($renderer);
		?>
		<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
		<head>
		<title><?php echo $statusMsg;?></title>
		<link rel="stylesheet" type="text/css" title="default" href="css/examine.css"/>
		<link rel="stylesheet" type="text/css" title="default" href="css/quickform.css"/>
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

/*
 * callback function triggered upon successful login
 * @param string $username the successful username
 * @param object $a the authentication object
 * @todo be sure to delete old data before inserting new data
 * @return void
 */
function successfulLogin($username=null,$a=null) {
	global $_POST;
	
	$a->log('successfulLogin begin');
	
    $db=createDB();
	$sql='UPDATE users SET last_login=NOW() WHERE user_id='.$a->getUserId();
    $db->exec($sql);
	$a->log('sql executed '.$sql);
    
	if (array_key_exists('remember',$_POST)) {
		$a->log('remember box checked - setting token');
		$a->updateToken();
	} else {
		$a->log('remember box not checked - no token activity');
	}
}

/*
 * callback function triggered upon successful logout
 * @param string $username the username of the just-logged-out user
 * @param object $a the authentication object
 */
function successfulLogout($username=null,$a=null) {
    // since the user has successfully logged out, we need to ensure that any token flags are cleared
    $a->setAuthData('checkedToken',false);
    $a->setAuthData('usedToken',false);
	$a->login();
}

/*
 * callback function triggered upon failed login
 * @param string $username the username of the attempted login
 * @param object $a the authentication object
 */
function failedLogin($username=null,$a=null) {
}

$a = &new myAuth('MDB2', $loginOptions,'loginForm');
$a->enableLogging=false;
$a->logger=&$log;
$a->setSessionname('chi_alpha_journey');
//$a->setIdle(900); // fifteen minutes
$a->setLoginCallback('successfulLogin');
$a->setLogoutCallback('successfulLogout');
$a->setFailedLoginCallback('failedLogin');
//$a->setCheckAuthCallback('checkAuthToken');

if (array_key_exists('logout',$_GET)) {
	$a->logout();
}

$a->start();

if (!$a->getAuth()) {
	//$a->log('not authorized - exiting');
	exit();
}
?>