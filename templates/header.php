<?php
$thisfile=basename($_SERVER['PHP_SELF']);
?>
<div id="header">
<ul id="primary">
<li><a href="index.php">main</a></li>
<?php 
	if ($thisfile=='events.php') {
		?>
		<ul id="secondary"><span>events</span>
		<li><a href="#" onclick="javascript:editmode()">edit</a></li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'];?>?action=delete&amp;event_id='<?php echo $event_id;?>' onclick="javascript:return confirm('Are you sure you want to delete this module?')">delete</a></li>
		<li><a href='<?php echo $_SERVER['PHP_SELF'];?>'?action=add>add a new event</a></li>
		</ul>
		<?php
	} else {
		echo '<li><a href="events.php">events</a></li>';
	}
?>
<li><a href="about.php">about</a></li>
<li><a href="<?php echo $_SERVER['PHP_SELF'];?>?logout=1">logout</a></li>
</ul>
</div>
