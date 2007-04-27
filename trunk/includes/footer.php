<div id="footer">
<?php
 $uname=posix_uname();
 echo 'Debug info: '.$uname['sysname'].' '.$uname['release'].' | PHP '.phpversion();
 //.'Apache '.apache_get_version().
?>
</div>