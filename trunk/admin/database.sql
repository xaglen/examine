#
# email list
#

# holds email addresses and preferences for mailing list
CREATE TABLE emails (
	emailID				int UNSIGNED NOT NULL AUTO_INCREMENT,
	email				varchar(50) NOT NULL,
	emailCategory		tinyint,					# from emailCategory
	fname				varchar(25),
	lname				varchar(25),
	groupName			varchar(25),
#	okToEmail			enum('y','n') NOT NULL DEFAULT 'y',
	format				enum('html','text') NOT NULL DEFAULT 'text',
	PRIMARY KEY (emailID),
	UNIQUE email (email)
);

# defines categories of emails (individual, church, etc.)
CREATE TABLE emailCategory (
	emailCatID			tinyint NOT NULL AUTO_INCREMENT,
	emailCat			varchar(25) NOT NULL,
	PRIMARY KEY (emailCatID),
	UNIQUE emailCat (emailCat)
);

# defines email lists that email addresses can receive mail from
CREATE TABLE emailLists (
	emailListID			tinyint NOT NULL AUTO_INCREMENT,
	emailListName		varchar(50) NOT NULL,
	emailListDesc		varchar(200),
	PRIMARY KEY (emailListID),
	UNIQUE emailList (emailListName)
);

# lists that emails are currently subscribed to
CREATE TABLE email_subscriptions (
	emailID				int UNSIGNED NOT NULL,
	emailListID			tinyint NOT NULL,
	PRIMARY KEY (emailID, emailListID)
);



#
# user authentication
#

CREATE TABLE users (
	uid					int(11) NOT NULL AUTO_INCREMENT,
	userName			varchar(25) NOT NULL,
	email				varchar(50) NOT NULL,
	passwd				varchar(50) NOT NULL,
	iv					varchar(50) NOT NULL,
	htpass				varchar(50) DEFAULT NULL,
	fname				varchar(25) DEFAULT NULL,
	lname				varchar(25) DEFAULT NULL,
	active				enum('y','n') NOT NULL DEFAULT 'n',
	PRIMARY KEY (uid),
	UNIQUE KEY email (email),
	UNIQUE KEY user (userName)
);

CREATE TABLE permissions (
	permissionID		tinyint NOT NULL AUTO_INCREMENT,
	permissionName		varchar(50) NOT NULL,
	permissionDesc		varchar(150),
	htpasswdLocation	varchar(100) DEFAULT NULL,
	PRIMARY KEY (permissionID),
	UNIQUE permission (permissionName)
);
INSERT INTO permissions SET permissionName='admin', permissionDesc='System administrator';
INSERT INTO permissions SET permissionName='database', permissionDesc='Can access and modify database';
INSERT INTO permissions SET permissionName='email', permissionDesc='Can send mass email on behalf of the site';
INSERT INTO permissions SET permissionName='content', permissionDesc='Can modify site content';
INSERT INTO permissions SET permissionName='leadership', permissionDesc='Can access leader-only protected areas of the site. (entered into .htpasswd file)';
INSERT INTO permissions SET permissionName='podcastAdmin', permissionDesc='Admin for podcast';
INSERT INTO permissions SET permissionName='podcast', permissionDesc='Submitter of a podcast (can modify what they submitted).';

CREATE TABLE user_permissions (
	uid					int NOT NULL,
	permissionID		tinyint NOT NULL,
	PRIMARY KEY (uid, permissionID)
);
INSERT INTO user_permissions SET uid='1', permissionID='1';



#
# content
#


CREATE TABLE content (
	contentID			int NOT NULL AUTO_INCREMENT,
	categoryID			tinyint,
	title				varchar(100),
	lastUpdated			timestamp,
	originalPost		timestamp,
	content				text,
	PRIMARY KEY (contentID)
);

CREATE TABLE contentCategory (
	categoryID			tinyint NOT NULL AUTO_INCREMENT,
	category			varchar(100),
	PRIMARY KEY (categoryID)
);
INSERT INTO contentCategory SET category='news';
INSERT INTO contentCategory SET category='blog';
INSERT INTO contentCategory SET category='spotlight';



#
# calendar
#


CREATE TABLE event (
	eventID				int NOT NULL AUTO_INCREMENT,
	category			tinyint NOT NULL,
	location			tinyint,
	eventName			varchar(50) NOT NULL,
	begin				DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	end					DATETIME DEFAULT NULL,
	day					varchar(9),
	PRIMARY KEY (eventID),
	UNIQUE event (category, eventName, begin)
);

CREATE TABLE eventDetails (
	eventID				int NOT NULL,		# from table event
	eventDetails		text,
	PRIMARY KEY eventID
);

CREATE TABLE eventLocation (
	locationID			int NOT NULL AUTO_INCREMENT,
	locationName		varchar(50),
	locationDetail		varchar(100),
	PRIMARY KEY (locationID),
	UNIQUE location (locationName)
);

CREATE TABLE eventCategory (
	catID				tinyint NOT NULL AUTO_INCREMENT,
	category			varchar(50),
	categoryDetail		varchar(100),
	PRIMARY KEY (catID),
	UNIQUE cat (category)
);


######################################## old
CREATE TABLE event (
	id					int(11) NOT NULL AUTO_INCREMENT,
	name				varchar(30) NOT NULL,
	eventDate			date NOT NULL DEFAULT '0000-00-00',
	startTime			time NOT NULL DEFAULT '00:00:00',
	endTime				time NOT NULL DEFAULT '00:00:00',
	location			int(11) DEFAULT NULL,
	description 		text DEFAULT NULL,
	category			int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY (id), KEY idx_date(eventDate)
) TYPE=MyISAM;

# a category of an event
# 	church service, Bible study, holiday, calendar info, ...
CREATE TABLE eventCategory (
	id					int(11) NOT NULL auto_increment,
	name				varchar(30) NOT NULL,
	PRIMARY KEY (id)
) TYPE=MyISAM;

# locations of events
CREATE TABLE location (
	id					int(11) NOT NULL auto_increment,
	name				varchar(30) NOT NULL,
	PRIMARY KEY (id)
) TYPE=MyISAM;

# queries
INSERT INTO eventCategory (name) VALUES ('');
INSERT INTO location (name) VALUES ('');
INSERT INTO event (name, eventDate, startTime, endTime, location, description, category)
	VALUES ('','','','','','','');
SELECT category.name, name, eventDate, eventDate, HOUR(startTime), MINUTE(startTime), endTime, location.name, description
	FROM event, eventCategory, location
	WHERE event.category=eventCategory.id AND event.location=location.id AND eventDate='';

SELECT eventID FROM event WHERE name='';
UPDATE event SET name='', description='' WHERE eventID='';
DELETE FROM event WHERE eventID='';
#############################################


# table to hold event (type)s
CREATE TABLE event (
	eventID				int NOT NULL AUTO_INCREMENT,
	name				varchar(50) NOT NULL UNIQUE,
	description			varchar(250),
	PRIMARY KEY (eventID)
);
# queries
INSERT INTO event (name, description) VALUES ('', '');
SELECT name, description FROM event WHERE eventID='';
SELECT eventID FROM event WHERE name='';
UPDATE event SET name='', description='' WHERE eventID='';
DELETE FROM event WHERE eventID='';

# table to hold calendar entries
CREATE TABLE calendar (
	date				DATE NOT NULL,
	day					varchar(9),
	beginTime			TIME NOT NULL,
	location			varchar(50) NOT NULL,
	eventID				int NOT NULL,
	PRIMARY KEY (date, beginTime, location, eventID)
);
# queries
INSERT INTO calendar (date, day, beginTime, location, eventID) 
	VALUES ('0000-00-00', 'Wednesday', '00:00:00', '', '');
SELECT date, day, beginTime, location FROM calendar WHERE eventID='';
UPDATE calendar SET day='', beginTime='', location='' WHERE eventID='' AND date='';
UPDATE calendar SET day='', beginTime='', location='' 
	WHERE eventID='' AND date='' AND beginTime='' AND location='';

# queries joining event and calendar
SELECT name, description, beginTime, location FROM calendar, event 
	WHERE date='' AND event.eventID=calendar.eventID;
SELECT date, day, beginTime, location FROM calendar, event 
	WHERE name='' AND event.eventID=calendar.eventID;
SELECT  FROM calendar, event WHERE ='';
