<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('CONTENT', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

require_once('dbFunctions.php');
require_once('DB.php');
require_once('../pageComponents/formTools.php');

require_once('adminConstructFunctions.php');

if ( $_GET['step'] == "process" && $_GET['run'] != 1 ) $msg = processContentEntry();
if ( $_GET['step'] == "completed" ) header("Location: menu.php?display=menu");

$pageTitle = "add a new content entry";
$pageName = "newContent";

printAdminHead($pageTitle, $pageName);
?>
<script language="javascript" type="text/javascript" src="<?php echo TINY_MCE; ?>"></script>
<script language="javascript" type="text/javascript">
		tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			elements : "content",
			theme_advanced_toolbar_location : "top",
			width : "390",
			height : "350",
			content_css : "<?php echo WEB_BASE."pageComponents/tinyMCEstyle.css"; ?>"
		});
</script>

<h1>Add new content</h1>

<p><a href="manageContent.php">Cancel</a></p>

<div class="divBox">
<br />
<?php

if ( $msg ) {
	echo '<p class="error">'."\n";
	foreach ( $msg as $m ) {
		echo $m."<br />\n";
	}
	echo '</p>'."\n\n";
}

?>
<form method="post" action="newContentEntry.php?step=process">
<table>
<tr>
	<td><strong>Category: </strong></td>
	<td>
<?php
$catDefault = "select a category";
$catOrder = "ORDER BY category";
makeDropDown ("category", "contentCategory", "category", "categoryID", $catDefault, $catOrder, $_POST['category']);
?>
	</td>
</tr>
<tr>
	<td><strong>Title: </strong></td>
	<td><input type="text" name="title" size="50" maxsize="100" value="<?php echo $_POST['title']; ?>" /></td>
</tr>
<tr>
	<td><strong>Content: </strong></td>
	<td><textarea name="content" id="content" rows="15" cols="50"><?php echo $_POST['content']; ?></textarea></td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" name="submit" value="submit" class="button" /></td>
</tr>
</table>
</form>
</div>

<p><a href="manageContent.php">Cancel</a></p>

<?php
printAdminBottom($pageName);


/***************
 * processContentEntry()
 ***************/
function processContentEntry () {
	if ( !$_POST['category'] ) {
		$msg['category'] = "You must select a category.";
	}
	if ( !$_POST['title'] || !validText($_POST['title']) ) {
		$msg['title'] = "The title is either empty or contains an invalid character.";
	}
//	if ( !$_POST['content'] || !looseText($_POST['content']) ) {
//		$msg['content'] = "The content you entered contains an invalid character.";
//	} else {
		$_POST['content'] = str_replace("</p><p>", "\n</p>\n\n<p>\n", $_POST['content']);
//	}


	// if valid, show confirmation page
	if ( count($msg) == 0 ) {
		$_GET['step'] = "feedback";
		$_GET['run'] = 1;
		submitData();
	}

	// if invalid, go back to the form
	return($msg);
}



/**************
 * submitData()
 * All data has passed validation checks and now will be inserted into the database
 **************/
function submitData () {
	
/*	// insert a new content entry
	$insertEntry = "INSERT INTO content (categoryID, title, date, content) VALUES ('" 
			. $_POST['category'] . "','" . $_POST['title'] . "','" . date('Y-m-d')  
			. "','" . $_POST['content'] . "')";
*/
	$dsn = unserialize(DSN);	// connection info for use in PEAR

	$db =& DB::connect($dsn);	// connect to database

	if ( PEAR::isError($db) ) {	// if error connecting, abort
		die($db->getMessage());
	}

	// use prepare() and execute() to insert the data
	$insertQuery = 'INSERT INTO content (categoryID, title, lastUpdated, originalPost, content) VALUES (?,?,?,?,?)';
	$sth = $db->prepare($insertQuery);

	$data = array($_POST['category'], $_POST['title'], NULL, NULL, $_POST['content']);

	$db->execute($sth, $data);

	if ( PEAR::isError($db) ) {	// if error inserting, abort
		die($db->getMessage());
	}
	$db->disconnect();			// disconnect from the database
/*	if ( !commandQuery($insertEntry) ) {
		echo '<p class="error">Error inserting the content entry.</p>'."\n";
		exit;
	} 
*/
	// if I'm here, everything worked
	$_GET['step'] = "completed";
}

/*
// select a specific content entry
$selectEntry = "SELECT category, title, lastUpdated, content FROM content"
		. " WHERE contentID='" . $contentID . "'";

// update a specific content entry
$updateEntry = "UPDATE content SET category='" . $_POST['category'] . "' title='" 
			. $_POST['title'] . "', lastUpdated='" . $lastUpdated . "', content='" . $_POST['content'] 
			. "' WHERE contentID='" . $contentID} . "'";

// get 5 most recent entries in a given category
$getEntries = "SELECT contentID, title, lastUpdated, content FROM content"
		. " WHERE category='" . $_POST['category'] . "' ORDER BY lastUpdated DESC LIMIT 5";

// get most recent entry in a given category
$getEntry = "SELECT contentID, title, lastUpdated, content FROM content"
		. " WHERE category='" . $_POST['category'] . "' ORDER BY lastUpdated DESC LIMIT 1";

// list the 5 most recent entries in a given category
$getEntryList = "SELECT contentID, title, lastUpdated FROM content"
		. " WHERE category='" . $_POST['category'] . "' ORDER BY lastUpdated DESC LIMIT 5";
*/

?>