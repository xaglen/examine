<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('EMAIL', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

include_once('dataCheckFunctions.php');
require_once('dbInfo.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');
require_once('../pageComponents/definitions.php');
include_once('../news/newsletters/emailTemplate.php');

$pageTitle = "HTML email";
$pageName = "HTMLemail";

printAdminHead($pageTitle, $pageName);
?>
<script language="javascript" type="text/javascript" src="<?php echo TINY_MCE; ?>"></script>
<script language="javascript" type="text/javascript">
		tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			elements : "emailBody,emailPictures",
//			plugins : "save",
//			theme_advanced_buttons3_add : "save",
			theme_advanced_toolbar_location : "top",
			width : "390",
			height : "350",
			content_css : "<?php echo WEB_BASE."news/newsletters/emailStylesheet.css"; ?>"
		});
</script>

<h1>Compose an email</h1>

<?php
if ( $_GET['step']=="review" ) {
	echo '<p>Review your email, make any needed corrections and then click the "send email" button below.</p>';
	reviewMail();
} elseif ( $_GET['step']=="send" ) {
	echo '<p>Your email has been sent.</p>';
	$result=sendMail(); echo $result;
} else {
	echo '<p>This page allows you to send an <strong>html-formatted</strong> email out to the mailing list.</p>';
}
?>

<hr />

<br />

<p><a href="menu.php?display=menu">Cancel</a></p>


<div class="divBox">

<form method="post" action="email_html.php?step=review">
	<h2>Email content:</h2><br />
	Subject: <input type="text" name="subject" id="mailSubject" <?php if ( $_POST['subject'] ) echo 'value="'.$_POST['subject'].'" '; ?>/><br /><br />
	Message content:<br /><br />
	<textarea id="emailBody" name="emailBody" cols="65" rows="15"><?php echo $_POST['emailBody']; ?></textarea><br /><br />
	Pictures:<br /><br />
	<textarea id="emailPictures" name="emailPictures" cols="65" rows="15"><?php echo $_POST['emailPictures']; ?></textarea><br /><br />
	<hr /><br />
	<input name="submit" type="submit" value="preview" class="button" />
</form>
</div>

<p><a href="menu.php?display=menu">Cancel</a></p><br />

<?php
printAdminBottom($pageName);


function assembleMessage () {
	$emailMessage .= MESSAGE_TOP;

	// display recipient's name
	$emailMessage .= "<p>{NAME},</p>\n\n";

	// add content from form to the message
	$emailMessage .= $_POST['emailBody'];

	// if html email, get top of template
	$emailMessage .= MESSAGE_MIDDLE;

	// add content from form to the message
	$formattedImages = fixImages($_POST['emailPictures']);
	$emailMessage .= $formattedImages;

	// if html email, get bottom of template
	$emailMessage .= MESSAGE_BOTTOM;

	return($emailMessage);
}



/**************
 * reviewMail()
 * allows user to review the email before sending it
 **************/
function reviewMail () {
	if ( $_POST['subject'] && $_POST['emailBody'] ) {
		echo "<p><strong>*** *** begin email preview *** ***</strong></p>\n";

		$emailMessage = assembleMessage();
		echo "<br />\n\n";

		echo $emailMessage."\n\n";
		echo "<br /><br />\n\n";
		
		echo "<p><strong>*** *** end email preview *** ***</strong></p>\n";

		// form for sending the email
?>

<form method="post" action="email_html.php?step=send">
	<input style="display:none" type="text" name="subject" value="<?php echo $_POST['subject']; ?>" />
	<textarea style="display:none" name="emailBody" cols="65" rows="15"><?php echo $_POST['emailBody']; ?></textarea>
	<textarea style="display:none" name="emailPictures" cols="65" rows="15"><?php echo $_POST['emailPictures']; ?></textarea>
	<input type="submit" name="submit" value="send email" class="button" />
</form>
<?php
	} else {
		$displayMessage = '<p class="error">Error: Both the subject and email body must have something filled in.  They cannot be empty.</p>'."\n";
	}
}



/**************
 * sendMail()
 * sends the email message
 **************/
function sendMail () {
	$from = SITE_EMAIL;

	// To send HTML mail, the Content-type header must be set
	$mailheaders = 'MIME-Version: 1.0' . "\r\n";
	$mailheaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	$mailheaders .= 'From: "Jerry Gibson" <' . $from . ">\r\n";
	$mailheaders .= 'Reply-To: ' . $from . "\r\n";
	$mailheaders .= 'Return-Path: ' . $from . "\r\n";
	$mailheaders .= 'Return-Receipt-To: ' . $from;

	// get list of email addresses
	$query = "SELECT email, fname FROM emailList WHERE okToEmail='1' AND email LIKE '%@%'";

	// get users info
	$mailList = returnQuery($query);

	if ( $mailList === FALSE ) {
		$displayMessage = '<p class="error">Error: could not access email list.</p>'."\n";
	} else {	// display table of stored user information
		$displayMessage = '<p><strong>Recipients:</strong></p>'."\n";

//for testing...
//$to = array('bkloef@yahoo.com', 'bkloef@gmail.com');
//$toCount = count($to);

		// for every recipient, assemble the email message and send it
		while ( $row = $mailList->fetchRow() ) {

//for testing
//while ( $row = $mailList->fetchRow() && $toCount>0) {

			$emailMessage = assembleMessage();

			// add email removal link
			$emailMessage .= "<br><br>";
			$emailMessage .= "\n\nIf you wish to be removed from this mailing list and receive no further emails:\n";
			$emailMessage .= '<a href="'.WEB_BASE.'pageComponents/manageEmailList.php?action=rem&email={EMAIL}">click this link</a>.'."\n";
	
			// replace place holders with data
			$emailMessage = str_replace('{EMAIL}', $row[0], $emailMessage);
			$emailMessage = str_replace('{NAME}', $row[1], $emailMessage);

			set_time_limit(0);	// reset timer so processing does not quit before completing

			// forces the From Address to be used
			ini_set(sendmail_from, $from);

//for testing...
//echo $to[$toCount-1]."<br />\n";

			// send email:  to address, subject, email body, mail headers, additional parameter
//			mail($row[0], stripslashes($_POST['subject']), stripslashes($emailMessage), $mailheaders, '-f'.$from);

//for testing
//mail($to[$toCount-1], stripslashes($_POST['subject']), stripslashes($emailMessage), $mailheaders, '-f'.$from);
//$toCount--;

			// sets value back to its default
			ini_restore(sendmail_from);

			$displayMessage .= $row[0]."<br />\n";

			// reset $emailMessage for next recipient
			$emailMessage = "";
		}
			
		$_POST['subject'] = '';
		$_POST['emailBody'] = '';
		$_POST['emailPictures'] = '';
	}

	return $displayMessage;
}


/**********
 * images from tinyMCE have the src as a relative path
 * add in full path before sending email
 **********/
function fixImages ( $emailPics ) {
	$emailPics = str_replace('src="/', 'src="'.WEB_BASE, $emailPics);
	$emailPics = str_replace('src="../', 'src="'.WEB_BASE, $emailPics);
	$emailPics = str_replace('<img', '<img width="150"', $emailPics);
	return ($emailPics);
}
 
?>
