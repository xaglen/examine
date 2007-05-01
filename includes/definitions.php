<?php
/**
 * this file contains constant definitions
 *
 * @package undecided
 * @author Brian Kloefkorn
 */
// system directory
define ( 'SYSTEM_BASE',		"g:/ApacheDocs/ChiAlpha/" );// /www/vhosts/chialpha.com/htdocs/ , /Users/bkloef/Sites/ChiAlpha/ , c:\\Apache\\Apache\\htdocs\\ChiAlpha\\
define ( 'COMPONENTS', 		SYSTEM_BASE."pageComponents/" );
//define ( 'SOURCE_FILES', 	COMPONENTS."pageSource/" );

// web directory
define ( 'WEB_BASE', 		"http://192.168.0.7/ChiAlpha/" );// http://www.chialpha.com/ , http://localhost/~bkloef/ChiAlpha/
define ( 'IMAGES', 			WEB_BASE."images/" );
//define ( 'GALLERY', 		WEB_BASE."gallery/" );
//define ( 'GALLERY_ADMIN',	GALLERY."admin/" );
//define ( 'CALENDAR',		WEB_BASE."calendar/" );
define ( 'ADMIN',			WEB_BASE."login/" );

// page list
define ( 'PAGE_LIST',		serialize(array('home','about','connect','resources','conferences','store','team')) );
define ( 'BOTTOM_LINKS',	serialize(array(
								array('contact us',				WEB_BASE.'connect/index.php?display=contact'),
								array('group locator',			WEB_BASE.'connect/locator/'),
								array('charter a group',		WEB_BASE.'team/XAworkers/affiliation/index.php?display=affiliation'),
								array('ministry opportunities',	WEB_BASE.'ministryOpportunities/'),
								array('links',					WEB_BASE.'resources/index.php?display=links'),
								/*array('charter your group',WEB_BASE.'team/index.php?display=charter'),
								array('internships','http://www.chialphainternship.com/'),
								array('Assemblies of God','http://www.ag.org/'),*/
								array('financial support',		WEB_BASE.'team/index.php?display=support'))
							)
);

// other files
define ( 'MAIN_STYLE',		WEB_BASE."pageComponents/stylesheet.css" );
define ( 'STYLE_HOME',		WEB_BASE."pageComponents/stylesheet_home.css" );
define ( 'STYLE_LEFTMENU',	WEB_BASE."pageComponents/stylesheet_leftMenu.css" );
define ( 'STYLE_OPENWIN',	WEB_BASE."pageComponents/stylesheet_openWin.css" );
define ( 'ADMIN_STYLE',		ADMIN."stylesheet.css" );
define ( 'TINY_MCE',		WEB_BASE."pageComponents/tinymce/jscripts/tiny_mce/tiny_mce.js" );
define ( 'TINY_MCE_STYLE',	WEB_BASE."pageComponents/tinyMCE.css" );

// other info
define ( 'SITE_NAME', 		"Chi Alpha Campus Ministries" );
define ( 'SITE_EMAIL',		"chialpha@ag.com" );
define ( 'CONTACT_EMAIL',	"webmaster@chialpha.com" );

?>
