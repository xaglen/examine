<?php
/**
 * This file contains some routines to help with debugging
 * It replaces the PEAR error handler at the bottom of the file
 *
 * @package journey
 * @subpackage library
 * @author Glen Davis
 */
require_once 'PEAR.php';
require_once 'Log.php';

$logfile = &Log::singleton('file', '/tmp/examine_log',
        'PHP', 
        array('mode' => 0666), PEAR_LOG_DEBUG);

$logfire = &Log::singleton('firebug', '', 
        'PHP',
        array('buffering' => true),
        PEAR_LOG_DEBUG);

$logdefault = &Log::singleton('error_log', PEAR_LOG_TYPE_SYSTEM);

$logconsole = &Log::singleton('display', '', '',array('error_prepend' => '<font color="#ff0000"><tt>', 'error_append'  => '</tt></font>'), PEAR_LOG_ERR);

$log = &Log::singleton('composite');
$log->addChild($logfile);
$log->addChild($logfire);
$log->addChild($logdefault);
$log->addChild($logconsole);

/**
 * Renders the error message
 *
 * @param string $message the error message
 * @param string $subject subject line if the report is being emailed
 * @return void
 */
function dumpError($message='An unknown error occurred',$subject='Website PEAR Error') {
    global $_SERVER;
    $message .="\n\n\n";
    $message .= 'script: '.$_SERVER['SCRIPT_FILENAME']."\n";
    $message .= 'ip: '.$_SERVER['REMOTE_ADDR']."\n";
    //    $headers = 'From: scripts@glenandpaula.com' . "\n";
    //     mail('glen.davis@gmail.com', $subject ,$message,$headers);
    echo "<pre>$message</pre>";
}

/**
 * Creates a text dump of all the arguments
 *
 * @param array $arguments array of all the arguments to a function
 * @return string 
 */
function dumpArgs($arguments=null) {
	$args = '';
    foreach ($arguments as $a) {
        $args .= "\n\t";
        switch (gettype($a)) {
        case 'integer':
        case 'double':
            $args .= $a;
            break;
        case 'string':
            $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
            $args .= "\"$a\"";
            break;
        case 'array':
            $args .= 'Array('.count($a).')';
            break;
        case 'object':
            $args .= 'Object('.get_class($a).')';
            break;
        case 'resource':
            $args .= 'Resource('.strstr($a, '#').')';
            break;
        case 'boolean':
            $args .= $a ? 'True' : 'False';
            break;
        case 'NULL':
            $args .= 'Null';
            break;
        default:
            $args .= 'Unknown';
        }
    }
    return $args;
}

/**
 * Error handler replacement for PEAR
 *
 * @param object &$obj an error object
 * @return void
 */
function pearError(&$obj) {
    $message = 'Standard Message: ' . $obj->getMessage() . "\n";
    $message.= 'Standard Code: ' . $obj->getCode() . "\n";
    $message.= 'Error Type: ' . $obj->getType()."\n";
    $message.= 'User Message: ' . $obj->getUserInfo() . "\n";
    $message.= 'Debug Message: ' . $obj->getDebugInfo() . "\n";
    $message.= 'Call Stack - using debug_backtrace():' ."\n";
    $callstack=debug_backtrace();
    foreach ($callstack as $call) {
        if (!isset($call['file'])) $call['file'] = '[PHP Kernel]';
        if (!isset($call['line'])) $call['line'] = '';
        $message.= $call['file'].' line '.$call['line'].' (function '.$call['function'].")";
        if (isset($call['args'])) $message.=dumpArgs($call['args'])."\n\n";
    }

    switch ($obj->getCode()) {
        case E_WARNING:
        case E_USER_WARNING:
            $priority = PEAR_LOG_WARNING;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $priority = PEAR_LOG_NOTICE;
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $priority = PEAR_LOG_ERR;
            break;
        default:
        $priority = PEAR_LOG_INFO;
    }

    $log = &Log::singleton('composite');
    $log->log($message,$priority);
    dumpError($message);
}

/**
 * Error handler replacement that writes errors to Firebug
 *
 * @return void
 */
function handleError($code, $message, $file, $line) {
    /* Map the PHP error to a Log priority. */
    switch ($code) {
        case E_WARNING:
        case E_USER_WARNING:
            $priority = PEAR_LOG_WARNING;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $priority = PEAR_LOG_NOTICE;
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $priority = PEAR_LOG_ERR;
            break;
        default:
            $priority = PEAR_LOG_INFO;
    }
    $log = &Log::singleton('composite');

if ($priority!=PEAR_LOG_INFO) {
     // too many warnings from MDB2 if we print INFO
     $log->log($message . ' in ' . $file . ' at line ' . $line,
             $priority);
 }
 if ($priority==PEAR_LOG_ERR) {
     $callstack=debug_backtrace();
     foreach ($callstack as $call) {
         if (!isset($call['file'])){
             $call['file'] = '[PHP Kernel]';
         }
         if (!isset($call['line'])) {
             $call['line'] = '';
         }
         $message.= "\n\n".$call['file'].' line '.$call['line'].' (function '.$call['function'].")";
         if (isset($call['args'])) {
                 $message.=dumpArgs($call['args'])."\n\n";
         }
     }
     $log->log($message . ' in ' . $file . ' at line ' . $line,$priority);
     die($message . ' in ' . $file . ' at line ' . $line);
 }
}

/**
 * remove variables with security implications before dumping the data
 *
 * @param array $defined_vars all the variables in the scope of the calling function
 * @return array
 */
function stripSensitiveVariables($defined_vars=null) {
    if (!isset($defined_vars) || !is_array($defined_vars)) {
        return null;
    }
    $keys=array_keys($defined_vars);
    foreach ($keys as $key) {
        if ($key{0}=='_') {
            unset($defined_vars[$key]);
        }
    }
    unset($defined_vars['user']);
    unset($defined_vars['pass']);
    unset($defined_vars['host']);
    unset($defined_vars['db']);
    unset($defined_vars['db_name']);
    unset($defined_vars['dsn']);
    unset($defined_vars['GLOBALS']);
    unset($defined_vars['PHPSESSID']);
    unset($defined_vars['HTTP_GET_VARS']);
    unset($defined_vars['HTTP_POST_VARS']);
    unset($defined_vars['HTTP_COOKIE_VARS']);
    unset($defined_vars['HTTP_SERVER_VARS']);
    unset($defined_vars['HTTP_ENV_VARS']);

    return $defined_vars;
}

/**
 * for putting non-PEAR errors through the same process - useful for debug-type statements
 *
 * @param string$message a description of the error
 * @param array $defined_vars the variables in scope at the time of the error
 * @return void
 */
function logError($message='non-PEAR Error',$defined_vars=null) {
    $message.="\n\n";
    if (isset($defined_vars) && is_array($defined_vars)) {
        $message.="Variables\n";
        $defined_vars=stripSensitiveVariables($defined_vars);
        $message.=print_r($defined_vars,true)."\n\n";
    }
    $message.="Call Stack\n";
    $callstack=debug_backtrace();
    foreach ($callstack as $call) {
        if (!isset($call['file'])) $call['file'] = '[PHP Kernel]';
        if (!isset($call['line'])) $call['line'] = '';
        $message.= $call['file'].' line '.$call['line'].' (function '.$call['function'].")";
        if (isset($call['args'])) $message.=dumpArgs($call['args'])."\n\n";
    }
    dumpError($message,'non-PEAR Error');
}

function dumpVariable(&$var=null, $scope=false, $prefix='unique', $suffix='value') {
    if($scope) $vals = $scope;
    else      $vals = $GLOBALS;

    $old = $var;
    $var = $new = $prefix.mt_rand().$suffix;
    $vname = FALSE;
    
    foreach($vals as $key => $val) {
        if($val === $new) $vname = $key;
    }

    $var = $old;

    $log = &Log::singleton('composite');
    $log->log($vname.': '.print_r($var,true));
        //print_r($variable);
}


PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,'pearError');
set_error_handler('handleError');
?>