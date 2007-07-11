<div id="header">
<ul id="primary">
<?php 

$pages['index.php']='main';
$pages['events.php']='events';
$pages['about.php']='about';
$pages['index.php?logout=1']='logout';

$thispage=array_shift(explode('?', basename($_SERVER['PHP_SELF'])));

while (list($page,$label)=each($pages)) {
	if ($thispage==$page) {
		echo "<li><span>$label</span><ul id='secondary'><li></li></ul></li>";
	} else {
		echo "<li><a href='$page'>$label</a></li>";
	}
}
?>
</ul>
</div>
