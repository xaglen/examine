<?php
require_once 'PEAR.php';
error_reporting(E_ALL | E_STRICT | E_NOTICE);

function dumpError($message='An unknown error occurred',$subject='Website PEAR Error') {
    global $_SERVER;
    $message .="\n\n\n";
    $message .= 'script: '.$_SERVER['SCRIPT_FILENAME']."\n";
    $message .= 'ip: '.$_SERVER['REMOTE_ADDR']."\n";
    //    $headers = 'From: scripts@glenandpaula.com' . "\n";
    //     mail('glen.davis@gmail.com', $subject ,$message,$headers);
    echo "<pre>$message</pre>";
}

function dumpArgs($arguments=null) {
    //takes an array
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