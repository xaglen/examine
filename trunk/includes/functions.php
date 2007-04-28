<?php
require_once 'config.php';

function getName($people_id=NULL) {
	$db=createDB();
	$sql='select preferred_name,first_name,last_name FROM people WHERE people_id='.$people_id;
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'].' '.$row['last_name'];
	} else {
		return $row['preferred_name'].' '.$row['last_name'];
	}
}

function generateNameArray($ministry_id=NULL) {
	$db=createDB();
	$sql='select p.people_id,p.first_name,p.last_name FROM people p,ministry_people mp WHERE mp.ministry_id='.$ministry_id.' AND mp.people_id=p.people_id';
	$result = $db->query($sql);
	//testQueryResult($result,$sql);
	while ($row=$result->fetchRow()) {
		$people[$row['people_id']]=$row['first_name'].' '.$row['last_name'];
	}
	return $people;
}

function getFirstName($people_id=NULL) {
	$db=createDB();
	$sql='select first_name,preferred_name FROM people WHERE people_id='.$people_id;
	$result=$db->query($sql);
	$row=$result->fetchRow();
	if ($row['preferred_name']===NULL) {
		return $row['first_name'];
	} else {
		return $row['preferred_name'];
	}
}

function getLastName($people_id=NULL) {
	$db=createDB();
	$sql='select last_name FROM people WHERE people_id='.$people_id;
	$last_name=$db->getOne($sql);
	return $last_name;
}

function generatePeopleDropdown($ministry_id=NULL,$name='people_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select p.people_id,p.first_name,p.last_name FROM people p,ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.people_id=p.people_id ORDER BY p.last_name,p.first_name";
	$result=$db->query($sql);
	//testQueryResult($result,$sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['people_id']) {
			$dropdown.='<option value='.$row['people_id'].' selected>'.$row['first_name'].' '.$row['last_name']."\n";
		} else {
			$dropdown.='<option value='.$row['people_id'].'>'.$row['first_name'].' '.$row['last_name']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

function generateSchoolDropdown($ministry_id=NULL$name="",$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select s.school_id,s.name FROM schools s, people p, ministry_people mp WHERE mp.ministry_id=$ministry_id AND mp.people_id=p.people_id AND p.school_id=s.school_id ORDER BY s.name";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['SchoolID']) {
			$dropdown.='<option value='.$row['school_id'].' selected>'.$row['school_name']."\n";
		} else {
			$dropdown.='<option value='.$row['school_id'].'>'.$row['school_name']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

function generateCategoryDropdown($name='category_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select category_id,category FROM categories ORDER BY category";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['category_id']) {
			$dropdown.='<option value='.$row['category_id'].' selected>'.$row['category']."\n";
		} else {
			$dropdown.='<option value='.$row['category_id'].'>'.$row['category']."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}


function generateSubgroupDropdown($ministry_id=NULL,$name='subgroup_id',$default=NULL) {
	$dropdown="<select ID='$name' NAME='$name'>";
	$db=createDB();
	$sql="select s.subgroup_id,s.time,s.location FROM subgroups s, ministry_people mp, subgroup_people sp WHERE mp.ministry_id=$ministry_id AND sp.people_id=m.people_id AND s.subgroup_id=sp.subgroup_id";
	$result=$db->query($sql);
	if ($default===NULL) {
		$dropdown.='<option value="" selected>&nbsp;';
	} else {
		$dropdown.='<option value="">&nbsp;';
	}
	while($row=$result->fetchRow()) {
		if ($default==$row['subgroup_id']) {
			$dropdown.='<option value='.$row['subgroup_id'].' selected>'.getSubgroupName($row['subgroup_id'])."\n";
		} else {
			$dropdown.='<option value='.$row['subgroup_id'].'>'.getSubgroupName($row['subgroup_id'])."\n";
		}
	}
	$dropdown.='</select>';
	return $dropdown;
}

function getSubgroupName($subgroup_id=NULL) {
	if ($subgroup_id===NULL) return '';
	$db=createDB();
	$sql="select name FROM subgroups WHERE subgroup_id=$subgroup_id";
	$name=$db->getOne($sql);
	return $name;
}

function generatePeopleLivesearch($ministry_id=NULL) {
	$livesearch="<script>var peoplearray=new Array(";
	$people=generateNameArray($ministry_id);
	foreach($people as $person) {
		$livesearch.="'".addslashes($person)."',";
	}
	$livesearch=rtrim($livesearch,',');
	$livesearch.=');</script>';
	$livesearch.="\n";
	$livesearch.='<form name="peoplesearch" action="people.php"><input type="text" name="person" value ="" onfocus="actb(this,event,peoplearray);" autocomplete="off"><INPUT type="submit" name="create" value="assign"></form>';
	//              $livesearch.='<div align="left" class="box" id="autocomplete" style="WIDTH:100px;BACKGROUND-COLOR:#ccccff"></div>';
	//$livesearch.="\n";
	return $livesearch;
}

function attendedEvent($people_id=NULL,$event_id=NULL) {
	if ($people_id===NULL || $event_id===NULL) return false;
	$db=createDB();
	$sql="select * FROM event_attendance WHERE people_id=$people_id AND event_id=$event_id";
	$attended=$db->getOne($sql);
	if ($attended) {
		return true;
	} else {
		return false;
	}
}

function getevent_attendance($event_id=NULL) {
	if ($event_id===NULL) return 0;
	
	$db=createDB();
	$sql='select COUNT(*) FROM event_attendance WHERE event_id='.$event_id;
	$count=$db->getOne($sql);
	return $count;
}

//
//  obscureEmail()
//  Modifies an email address to make it less spam-scraper-friendly
//  usage: despam($email[, $linkText])
//  returns a complete mailto link
//
function obscureEmail($email) {
	$partA = substr($email, 0, strpos($email, '@'));
	$partB = substr($email, strpos($email, '@'));
	$partB = rtrim($partB);
	$linkText = (func_num_args() == 2) ? func_get_arg(1) : $email;
	$linkText = str_replace('@', '<span class="obscure">&#64;</span> ', $linkText);
	return '<a href="email" onClick=\'a="'.$partA.'";this.href="ma"+"il"+"to:"+a+"'.$partB.'";\'>'.$linkText.'</a>';
}
?>