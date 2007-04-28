<?php

session_start();

if ( !$_SESSION['validUser'] || !in_array('EMAIL', $_SESSION['permissions']) ) { header("Location: menu.php?display=menu"); exit; }

include_once('dataCheckFunctions.php');
require_once('dbFunctions.php');
require_once('DB.php');

require_once('adminConstructFunctions.php');
require_once('../pageComponents/definitions.php');

$pageTitle = "email";
$pageName = "email";

printAdminHead($pageTitle, $pageName);
?>

<h1>Compose an email</h1>

<?php
if ( $_GET['step']=="review" ) {
	echo '<p>Review your email, make any needed corrections and then click the "send email" button below.</p>';
	reviewMail();
} elseif ( $_GET['step']=="send" ) {
	echo '<p>Your email has been sent.</p>';
	$result=sendMail(); echo $result;
} else {
	echo '<p>This page allows you to send a <strong>plain text</strong> email out to the mailing list.</p>';
}
?>

<hr />

<br />

<p><a href="menu.php?display=menu">Cancel</a></p>

<div class="divBox">

	<form method="post" action="email_plain.php?step=review">
	<h2>Email content:</h2><br />
	Subject: <input type="text" name="subject" id="mailSubject" <?php if ( $_POST['subject'] ) echo 'value="'.$_POST['subject'].'" '; ?>/><br /><br />
	Message content:<br /><br />
	<textarea id="emailBody" name="emailBody" cols="65" rows="15"><?php echo $_POST['emailBody']; ?></textarea><br /><br />
	<hr /><br />
	<input name="submit" type="submit" value="preview" class="button" />
</form>
</div>

<p><a href="menu.php?display=menu">Cancel</a></p><br />

<?php
printAdminBottom($pageName);



/**************
 * reviewMail()
 * allows user to review the email before sending it
 **************/
function reviewMail () {
	if ( $_POST['subject'] && $_POST['emailBody'] ) {
		echo "<p><strong>*** *** begin email preview *** ***</strong></p>\n";

		$emailMessage .= "{NAME},\n\n";		// display recipient's name
		$emailMessage .= $_POST['emailBody'];	// add content from form to the message

		echo "<br />\n\n";

		// if not html, format for preview display on Web
		echo '<textarea id="preview" style="background:#ffffff; color:#333333;" cols="65" rows="15" disabled="disabled">'.$emailMessage.'</textarea>';

		echo "<br /><br />\n\n";
		
		echo "<p><strong>*** *** end email preview *** ***</strong></p>\n";

		// form for sending the email
?>

<form method="post" action="email_plain.php?step=send">

<input style="display:none" type="text" name="subject" value="<?php echo $_POST['subject']; ?>" />
<textarea style="display:none" name="emailBody" cols="65" rows="15"><?php echo $_POST['emailBody']; ?></textarea>
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
	$mailheaders .= 'From: "Jerry Gibson" <' . $from . ">\r\n";
	$mailheaders .= 'Reply-To: ' . $from . "\r\n";
	$mailheaders .= 'Return-Path: ' . $from . "\r\n";
	$mailheaders .= 'MIME-Version: 1.0' . "\r\n";
	$mailheaders .= 'Content-Type: text/plain; charset=ISO-8859-1' . "\r\n";

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

//for testing...
//while ( ($row = $mailList->fetchRow()) && $toCount>0) {

			if ( $row[1] ) $emailMessage = "{NAME},\n\n";	// display recipient's name if NOT a church (is a person)
			$emailMessage .= $_POST['emailBody'];	// add content from form to the message

			// add email removal link
			$emailMessage .= "\n\nIf you wish to be removed from this mailing list and receive no further emails:\n";
			$emailMessage .= WEB_BASE . "pageComponents/manageEmailList.php?action=rem&email={EMAIL}";
	
			// replace place holders with data
			$emailMessage = str_replace('{EMAIL}', $row[0], $emailMessage);
			$emailMessage = str_replace('{NAME}', $row[1], $emailMessage);

			set_time_limit(0);	// reset timer so processing does not quit before completing

			// forces the From Address to be used
			ini_set(sendmail_from, $from);


			// send email:  to address, subject, email body, mail headers, additional parameter
			mail($row[0], stripslashes($_POST['subject']), stripslashes($emailMessage), $mailheaders, '-f'.$from);

//for testing...
//mail($to[$toCount-1], stripslashes($_POST['subject']), stripslashes($emailMessage), $mailheaders, '-f'.$from);
//$toCount--;

			// sets value back to its default
			ini_restore(sendmail_from);

			$displayMessage .= $row[0]."<br />\n";

//for testing...
//$displayMessage .= $to[$toCount]."<br />\n";

			// reset $emailMessage for next recipient
			$emailMessage = "";
		}
			
		$_POST['subject'] = '';
		$_POST['emailBody'] = '';
		$_POST['emailPictures'] = '';
	}

	return $displayMessage;
}

?>
