<?php
$thisfile=array_shift(explode('?', basename($_SERVER['PHP_SELF'])));
?>
<div id="header">
<ul id="primary">
<?php 

// there's got to be a better way to do this - google for PHP CSS menus sometime

if ($thisfile=='index.php') {
	echo '<li><span>main</span><ul id="secondary"><li></li></ul></li>';
} else {
	echo '<li><a href="index.php">main</a></li>';
}

if ($thisfile=='events.php') {
		?>
		<!-- this turns out to not be very helpful...
		<li><span>events</span>
		<ul id="secondary">
		<li><a href="#" onclick="javascript:editmode()">edit</a></li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'];?>?action=delete&amp;event_id='<?php echo $event_id;?>' onclick="javascript:return confirm('Are you sure you want to delete this module?')">delete</a></li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'];?>'?action=add>add a new event</a></li>
		</ul>
		-->
		<?php
		echo '<li><span>events</span><ul id="secondary"><li></li></ul></li>';
	} else {
		echo '<li><a href="events.php">events</a></li>';
	}
	
if ($thisfile=='about.php') {
	echo '<li><span>about</span><ul id="secondary"><li></li></ul></li>';
} else {
	echo '<li><a href="about.php">about</a></li>';
}
?>
<li><a href="<?php echo $_SERVER['PHP_SELF'];?>?logout=1">logout</a></li>
</ul>
</div>
