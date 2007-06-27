<?php
/**
 * A tool to make viewing the error log easier
 * @package journey
 * @subpackage library
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/examine.css" type="text/css">
<link rel="stylesheet" href="css/quickform.css" type="text/css">
<title>Error Log</title>
</head>
<body>
<?php
require_once 'File.php';
require_once 'HTML/QuickForm.php';

$file = "/tmp/examine_log";

if (array_key_exists('wipe',$_GET)) {
    $fp = new File();

    //Write a single line to the file, using a Macintosh EOL character and
    ////truncating the file before writing to it
    $fp->writeLine($file, 'Journey log file was wiped and restarted on '.date(DATE_RFC822), FILE_MODE_WRITE);
}

$form =& new HTML_QuickForm('wipe','GET',$_SERVER['PHP_SELF']);
$group[]=&HTML_QuickForm::createElement('xbutton', 'refresh', '<img src="graphics/icons/information.png" height="16" width="16"/> Refresh', array('class'=>'positive','this.submit()'));
$group[]=&HTML_QuickForm::createElement('xbutton', 'wipe', '<img src="graphics/icons/cancel.png" height="16" width="16"/> Clear', array('class'=>'negative','onclick'=>'this.submit()'));
$form->addGroup($group, null, '', ' ');
//$form->addElement('submit','wipe','restart file?');
//$form->addElement('submit','refresh','refresh data?');
$form->display();

//Echo the whole file
echo '<pre>'.File::readAll($file).'</pre>';

$form->display();
?>
</body>
</html>
