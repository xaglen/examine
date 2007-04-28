<?php
require_once 'PEAR.php';

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
function handleError(&$obj) {
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
    dumpError($message);
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

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,'handleError');
?>
