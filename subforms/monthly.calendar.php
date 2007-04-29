<table class="calendar" id="calendar" name="calendar" border='0' cellpadding='0' cellspacing='0' width='150'>
<?php
## from http://www.zend.com/code/codex.php?id=692&single=1
## mail:  kghoker at yahoo.com  9/17/2001
## Much like "A Tiny Calendar", just less code.  
## Give it a month/day/year, and it will generate the calendar and highlight the day.  
## Colors are customizeable.  If no date arguments given, assumes today.
  $month = (isset($_REQUEST['month'])) ? $_REQUEST['month'] : date("n",time());
  $year = (isset($_REQUEST['year'])) ? $_REQUEST['year'] : date("Y",time());
  $today = (isset($_REQUEST['day']))? $_REQUEST['day'] : date("j", time());

  $numdays = date("t",mktime(1,1,1,$month,1,$year));
  $wdays = array('Su','Mo','Tu','We','Th','Fr','Sa');
  
  //$nextmonth=date('Y-m-d 00:00:00',mktime(0,0,0,$month+1,$today,$year));
  //$lastmonth=date('Y-m-d 00:00:00',mktime(0,0,0,$month-1,$today,$year));
?>
<tr><td colspan='7' valign='middle' align='center'>

<a href="#" onClick="ChangeCalendar('<?php echo $_REQUEST["datefield"]?>',<?php $lastmonth=$month-1;echo $lastmonth;?>,<?php echo $today;?>,<?php echo $year;?>)">
&lt;&lt;
</a>&nbsp;&nbsp;<b>
<?php echo date("M Y",mktime(1,1,1,$month,1,$year));?>
</b>&nbsp;&nbsp;
<a href="#" onClick="ChangeCalendar('<?php echo $_REQUEST["datefield"]?>',<?php $nextmonth=$month+1;echo $nextmonth;?>,<?php echo $today;?>,<?php echo $year;?>)">
&gt;&gt;
</a>
</td></tr>
<tr>
<?php
foreach($wdays as $value) {
  print("<td valign='middle' align='center' width='15%'><b>{$value}</b></td>\n");
}
?>
</tr><tr>
<?php
for ($i = 0; $i < $dayone = date("w",mktime(1,1,1,$month,1,$year)); $i++) {
  print("<td valign='middle' align='center' width='15%'>&nbsp;</td>\n");
}
for ($zz = 1; $zz <= $numdays; $zz++) {
  if ($i >= 7) {  print("</tr><tr>"); $i=0;  }
  $date=date('Y-m-d 00:00:00',mktime(0,0,0,$month,$zz,$year));
  if ($zz == $today) {
	$class='selectedDate';
  } else {
	$class='normalDate';
  }
  printf("<td valign='middle' align='center' width='15%%' class='%s' id='cal%d'><a href='#' onclick='SetDate(\"%s\",\"%s\",\"cal%d\")'>%d</a></td>\n",
			$class,$zz,$_REQUEST['datefield'],$date,$zz,$zz);
  $i++;
}  
?>
</tr>
</table>
