CREATE TABLE `_seq` (
  `sequence` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`sequence`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
CREATE TABLE `addresses` (
  `pid` int(11) NOT NULL,
  `label` varchar(32) NOT NULL default 'school',
  `street` varchar(255) NOT NULL,
  `apartment` varchar(8) default NULL,
  `city` varchar(32) NOT NULL,
  `state` char(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  PRIMARY KEY  (`pid`,`label`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category` varchar(25) NOT NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `contact_info_type` (
  `contact_info_type_id` int(11) NOT NULL,
  `contact_info_type` varchar(50) NOT NULL,
  PRIMARY KEY  (`contact_info_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='for storing new technologies that come along (Facebook, etc)';
CREATE TABLE `email_addresses` (
  `pid` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `receive_emails` tinyint(4) NOT NULL,
  `publish` tinyint(4) NOT NULL,
  `preferred` tinyint(4) NOT NULL,
  `label` varchar(32) NOT NULL default 'school',
  PRIMARY KEY  (`pid`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `event_attendance` (
  `pid` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY  (`pid`,`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `ministry_id` int(11) NOT NULL,
  `event_type` varchar(50) default NULL,
  `name` varchar(50) NOT NULL,
  `begin` datetime default NULL,
  `end` datetime default NULL,
  `salvations` int(11) NOT NULL default '0',
  `baptisms_in_hs` int(11) NOT NULL default '0',
  `estimated_attendance` int(11) NOT NULL default '0',
  `offering` float(9,2) NOT NULL default '0.00',
  `notes` mediumtext,
  PRIMARY KEY  (`event_id`),
  KEY `ministry_id` (`ministry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `ministries` (
  `ministry_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL default 'Chi Alpha Christian Fellowship',
  `url` varchar(255) NOT NULL,
  `parent_ministry_id` int(11) default NULL COMMENT 'foreign key to this table',
  `name_for_small_groups` varchar(64) NOT NULL default 'small groups',
  `name_for_dorms` varchar(64) NOT NULL default 'dorms',
  `name_for_terms` varchar(32) NOT NULL default 'semester',
  PRIMARY KEY  (`ministry_id`),
  KEY `parent_ministry_id` (`parent_ministry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='forms a tree structure owing to the parent_ministry_id field';
CREATE TABLE `ministry_people` (
  `ministry_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY  (`ministry_id`,`pid`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `other_contact_info` (
  `pid` int(11) NOT NULL,
  `contact_info_type_id` int(11) NOT NULL,
  `contact_info` varchar(255) NOT NULL,
  PRIMARY KEY  (`pid`,`contact_info_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='for storing contact info in not-yet-foreseen formats';
CREATE TABLE `people` (
  `pid` int(11) NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `middle_name` varchar(32) default NULL,
  `last_name` varchar(32) NOT NULL COMMENT 'nick name',
  `preferred_name` varchar(32) default NULL,
  `student_code` varchar(255) default NULL COMMENT 'assigned by school (id card)',
  `category_id` int(11) NOT NULL default '1',
  `major` varchar(32) default NULL,
  `school_id` int(11) default NULL,
  `birthdate` date default NULL,
  `gender` enum('m','f','?') NOT NULL default '?',
  `home_nation` varchar(32) default NULL,
  `citizenship` varchar(32) default NULL,
  `spouse_id` int(11) default NULL,
  `guest_of` int(11) default NULL,
  `class_of` char(4) default NULL,
  `selected` tinyint(4) NOT NULL default '0',
  `notes` mediumtext,
  `last_updated` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`pid`),
  KEY `last_updated` (`last_updated`),
  KEY `created_on` (`created_on`),
  KEY `name` (`first_name`,`middle_name`,`last_name`,`preferred_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `phone_numbers` (
  `pid` int(11) NOT NULL,
  `number` varchar(50) NOT NULL,
  `label` varchar(32) NOT NULL default 'cell',
  `publish` tinyint(4) default NULL,
  `receive_sms` tinyint(4) default NULL,
  `preferred` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`pid`,`number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role` varchar(32) NOT NULL,
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `url` varchar(255) NOT NULL,
  `search_url` varchar(255) NOT NULL COMMENT 'printf style string for searching school names online',
  PRIMARY KEY  (`school_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `search` (
  `pid` int(11) NOT NULL,
  `data` longtext NOT NULL COMMENT 'clump of all the persons data concatenated together',
  PRIMARY KEY  (`pid`),
  FULLTEXT KEY `data` (`data`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `subgroup_people` (
  `subgroup_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `subgroup_role_id` int(11) NOT NULL,
  PRIMARY KEY  (`subgroup_id`,`pid`),
  KEY `subgroup_role_id` (`subgroup_role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `subgroup_roles` (
  `subgroup_role_id` int(11) NOT NULL,
  `role` varchar(64) NOT NULL COMMENT 'in PHP scripts create default values using a SELECT box with an OTHER option so it can be customized',
  PRIMARY KEY  (`subgroup_role_id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bible study leader, vocalist, etc';
CREATE TABLE `subgroups` (
  `subgroup_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `time` time NOT NULL,
  `location` varchar(64) NOT NULL,
  `notes` mediumtext NOT NULL,
  PRIMARY KEY  (`subgroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='for worship teams, bible studies, etc';
CREATE TABLE `user_preferences` (
  `user_id` int(11) NOT NULL,
  `prefname` varchar(32) NOT NULL,
  `prefval` mediumtext,
  PRIMARY KEY  (`user_id`,`prefname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `user_remember_me` (
  `user_id` int(11) NOT NULL,
  `token` int(11) NOT NULL,
  `created_on` timestamp NOT NULL default CURRENT_TIMESTAMP,
  KEY `username` (`user_id`,`token`,`created_on`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='used for the remember me function on login';
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `privileges` varchar(255) default NULL,
  `last_login` timestamp NULL default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`,`pid`),
  KEY `last_login` (`last_login`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `variables` (
  `configname` varchar(32) NOT NULL,
  `configval` mediumtext NOT NULL,
  PRIMARY KEY  (`configname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='for system-wide variables';
