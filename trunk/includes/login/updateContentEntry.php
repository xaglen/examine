<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('CONTENT', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbFunctions.php');
require_once('DB.php');
require_once('../pageComponents/formTools.php');

require_once('adminConstructFunctions.php');

$pageTitle = "update a content entry";
$pageName = "updateContent";


printAdminHead($pageTitle, $pageName);
?>
<script language="javascript" type="text/javascript" src="<?php echo TINY_MCE; ?>"></script>
<script language="javascript" type="text/javascript">
		tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			elements : "editContent",
			theme_advanced_toolbar_location : "top",
			width : "390",
			height : "350",
			content_css : "<?php echo WEB_BASE."pageComponents/tinyMCEstyle.css"; ?>"
		});
</script>

<h1>Update content</h1>

<p><a href="manageContent.php">Cancel</a></p>

<?php

if ( $_GET['step'] == "search" ) {	// shows results of a search or error message if search was invalid
	if ( !$_POST['category'] && !$_POST['title'] && !$_POST['content'] )
		echo '<p class="error">You must enter at least one search criterion</p>'."\n";
	else $posts = searchContent();

	if ( $posts === false ) {	// error
		echo '<p class="error">'."\n";
		echo 'There was an invalid character in your search.'."\n";
		echo '</p>'."\n\n";
	}

	$searchForm = showSearchForm();
} elseif ( $_GET['step'] == "update" ) {	// shows form to update an entry
	showUpdateForm();
} elseif ( $_GET['step'] == "process" && $_GET['run'] != 1 ) {	// updates the entry or displays error messages
	$msg = processContentUpdate();

	if ( $msg != 'completed' ) {	// errors
		echo '<p class="error">'."\n";
		foreach ( $msg as $m ) {
			echo $m."<br />\n";
		}
		echo '</p>'."\n\n";

		showUpdateForm();
	} else {
		echo '<p class="confirmation">Your changes have been submitted</p>'."\n";
		showSearchForm();
	}
} else {	// default - shows form to search for existing content
	showSearchForm();
}
?>

<p><a href="manageContent.php">Cancel</a></p>

<?php
printAdminBottom($pageName);



//
// showUpdateForm()
// Show editable form for chosen content entry
//
function showUpdateForm () {
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	// use prepare() and execute() to insert the data
	$selectQuery = "SELECT categoryID, title, content FROM content "
				. "WHERE contentID='".$_POST['contentID']."'";
	$res =& $db->query($selectQuery);	// run the query

	if ( PEAR::isError($res) ) {	// if error inserting, abort
		die($res->getMessage());
	}
	$db->disconnect();			// disconnect from the database

	while ($res->fetchInto($row)) {	// collect the data
		$categoryID = $row[0];
		$title = $row[1];
		$content = $row[2];
	}

?>
<div class="divBox">
<h2>Edit existing content entry...</h2><br />
<form method="post" action="updateContentEntry.php?step=process">
<input type="hidden" name="contentID" value="<?php echo $_POST['contentID']; ?>" />
<table>
<tr>
	<td><strong>Category: </strong></td>
	<td>
<?php
$catDefault = "select a category";
$catOrder = "ORDER BY category";
if ( $_POST['editCategory'] ) $value = $_POST['editCategory']; else $value = $categoryID;
// name, DBtable, field, idField, default, order, value
makeDropDown ("editCategory", "contentCategory", "category", "categoryID", $catDefault, $catOrder, $value);
?>
	</td>
</tr>
<tr>
	<td><strong>Title: </strong></td>
	<td><input type="text" name="editTitle" size="50" maxsize="100" value="<?php if ($_POST['editTitle']) echo $_POST['editTitle']; else echo $title; ?>" /></td>
</tr>
<tr>
	<td><strong>Content: </strong></td>
	<td><textarea name="editContent" id="editContent" rows="15" cols="50"><?php if ($_POST['editContent']) echo $_POST['editContent']; else echo $content; ?></textarea></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" name="submit" value="update" class="button" /></td>
</tr>
</table>
</form>
</div>
<?php
}



//
// showSearchForm()
// Displays the search form to find content entries
//
function showSearchForm () {
?>
<div class="divBox">
<h2>Search for existing content...</h2><br />
<form action="updateContentEntry.php?step=search" method="post">
<table>
<tr>
	<td><strong>Category: </strong></td>
	<td>
<?php
	$catDefault = "category";
	$catOrder = "ORDER BY category";
	makeDropDown ("category", "contentCategory", "category", "categoryID", $catDefault, $catOrder, $_POST['category'])."\n"
?>
	</td>
</tr>
<tr>
	<td><strong>Title: </strong></td>
	<td><input type="text" name="title" size="50" maxsize="100" value="<?php echo $_POST['title']; ?>" /></td>
</tr>
<tr>
	<td><strong>Content: </strong></td>
	<td><input type="text" name="content" size="50" maxsize="50" value="<?php echo $_POST['content']; ?>" /></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" name="submit" value="search" class="button" /></td>
</tr>
</table>
</form>
</div>
<?php
}



//
// searchContent()
// 
//
function searchContent () {
	// validate entered text
	if ( !validText($_POST['title']) || !looseText($_POST['content']) ) {
		return false;
	}

	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	// use prepare() and execute() to insert the data
	$selectQuery = 'SELECT contentID, content.categoryID, category, title, originalPost, content FROM '
				. 'content, contentCategory WHERE content.categoryID=contentCategory.categoryID AND ';
	if ( $_POST['category'] ) { $selectWhere .= "content.categoryID='".$_POST['category']."',"; }
	if ( $_POST['title'] ) { $selectWhere .= "title LIKE '%".$_POST['title']."%',"; }
	if ( $_POST['content'] ) { $selectWhere .= "content LIKE '%".$_POST['content']."%',"; }
//	if ( $_POST['lastUpdated'] ) $selectWhere .= $_POST['lastUpdated'];

	// correctly format the $selectWhere part of the query and then put the entire query together
	$selectQuery = $selectQuery . str_replace(",", " AND ", rtrim($selectWhere, ","));
	$selectQuery .= " ORDER BY originalPost DESC";

	$res =& $db->query($selectQuery);	// run the query

	if ( PEAR::isError($res) ) {	// if error inserting, abort
		die($res->getMessage());
	}
	$db->disconnect();			// disconnect from the database

	while ($res->fetchInto($row)) {	// collect the data
		$posts[] = $row;
	}

	displayPosts($posts);	// display the table of matches
}



//
// displayPosts()
// Assemble a table to display content entries and allow selection for editing
// Input: 	rows - contentID, categoryID, category, title, originalPost, content
//
function displayPosts ( $posts ) {
?>
<div class="divBox">
<h2>Matching entries</h2><br />
<form action="updateContentEntry.php?step=update" method="post">
<table class="dataDisp">
<tr class="head">
	<td></td>
	<td><strong>Category</strong></td>
	<td><strong>Title</strong></td>
	<td><strong>Originally Posted</strong></td>
	<td><strong>Content</strong></td>
</tr>
<?php
	$rowNum = 1;	// for alternating line color
	foreach ( $posts as $row ) {
		if ( ($rowNum % 2) == 0 ) $class = 'dataEven'; else $class = 'data';

		$originalPost = readTimeStamp($row[4]);	// allow human-readable time and date info
		$contentSample = sampleContent($row[5], $x=100);	// display a portion of the content for this entry (first $x characters)
?>
<tr class="<?php echo $class; ?>">
	<td><input type="radio" name="contentID" value="<?php echo $row[0]; ?>"<?php if ($rowNum == 1) echo ' checked="checked"'; ?> /></td>
	<td><?php echo $row[2]; ?></td>
	<td><?php echo $row[3]; ?></td>
	<td><?php echo $originalPost['month'].'/'.$originalPost['day'].'/'.$originalPost['year'].'<br />'
					. $originalPost['hours'].':'.$originalPost['minutes'].':'.$originalPost['seconds']; ?></td>
	<td><?php echo $contentSample; ?></td>
</tr>
<?php
		$rowNum++;
	}
?>
<tr class="dataEven"><td colspan="5"></td></tr>
</table>
<input type="submit" name="submit" value="update" class="button" />
</form>
</div>
<?php	
}



//
// sampleContent ()
//
function sampleContent ( $content, $length ) {
	if ( !$length ) $length = 100;

	if ( strpos(trim($content), "<p>") === 0 ) $content = substr_replace($content, "", 0, 3);

	$content = str_replace("<p>", "<br /><br />", $content);
	$content = str_replace("</p>", "", $content);
	$content = str_replace("&nbsp;", " ", $content);
	$content = str_replace("\n", "", $content);

	$sample = substr($content, 0, $length);
	if ( strlen($content) > $length ) $sample .= "...";

	return $sample;
}



//
// readTimeStamp ()
//
function readTimeStamp ( $ts ) {
	$timestamp = array();

/*	$timestamp['year'] = substr($ts, 0, 4);
	$timestamp['month'] = substr($ts, 4, 2);
	$timestamp['day'] = substr($ts, 6, 2);
	$timestamp['hours'] = substr($ts, 8, 2);
	$timestamp['minutes'] = substr($ts, 10, 2);
	$timestamp['seconds'] = substr($ts, 12, 2);
*/
	$timestamp['year'] = substr($ts, 0, 4);
	$timestamp['month'] = substr($ts, 5, 2);
	$timestamp['day'] = substr($ts, 8, 2);
	$timestamp['hours'] = substr($ts, 11, 2);
	$timestamp['minutes'] = substr($ts, 14, 2);
	$timestamp['seconds'] = substr($ts, 17, 2);

	return $timestamp;
}



/***************
 * processContentUpdate()
 ***************/
function processContentUpdate () {
	if ( !$_POST['editCategory'] ) {
		$msg['category'] = "You must select a category.";
	}
	if ( !$_POST['editTitle'] || !validText($_POST['editTitle']) ) {
		$msg['title'] = "The title is either empty or contains an invalid character.";
	}
	if ( !$_POST['editContent'] || !looseText($_POST['editContent']) ) {
		$msg['content'] = "The content you entered contains an invalid character.";
	} else {
		$_POST['editContent'] = str_replace("</p><p>", "\n</p>\n\n<p>\n", $_POST['editContent']);
	}

	// if valid, show confirmation page
	if ( count($msg) == 0 ) {
		$_GET['step'] = "feedback";
		$_GET['run'] = 1;
		submitData();
		return 'completed';
	}

	// if invalid, go back to the form
	return($msg);
}



/**************
 * submitData()
 * All data has passed validation checks and now will be inserted into the database
 **************/
function submitData () {
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	// use prepare() and execute() to insert the data
	$sth = $db->prepare('UPDATE content SET categoryID=?, title=?, lastUpdated=NOW(), content=? WHERE contentID=?');

	$data = array($_POST['editCategory'], $_POST['editTitle'], $_POST['editContent'], $_POST['contentID']);

	$db->execute($sth, $data);

	if ( PEAR::isError($db) ) {	// if error inserting, abort
		die($db->getMessage());
	}
	$db->disconnect();			// disconnect from the database

	return;
}

?>