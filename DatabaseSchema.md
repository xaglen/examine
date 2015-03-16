_the tables are (hopefully) laid out in compliance with our CodingStandards_

# The Schema #

```
-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Apr 27, 2007 at 06:29 PM
-- Server version: 5.0.27
-- PHP Version: 5.1.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `examine`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `categories`
-- 

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category` varchar(25) NOT NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `events`
-- 

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL auto_increment,
  `event_type` varchar(50) default NULL,
  `name` varchar(50) NOT NULL,
  `begin` datetime default NULL,
  `end` datetime default NULL,
  `salvations` int(11) NOT NULL default '0',
  `baptisms_in_hs` int(11) NOT NULL default '0',
  `estimated_attendance` int(11) NOT NULL default '0',
  `offering` int(11) NOT NULL default '0',
  `notes` mediumtext,
  PRIMARY KEY  (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `event_attendance`
-- 

CREATE TABLE `event_attendance` (
  `people_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  PRIMARY KEY  (`people_id`,`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `ministries`
-- 

CREATE TABLE `ministries` (
  `ministry_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default 'Chi Alpha Christian Fellowship',
  `url` varchar(255) NOT NULL,
  `parent_ministry_id` int(11) default NULL COMMENT 'foreign key to this table',
  `name_for_small_groups` varchar(64) NOT NULL default 'small groups',
  `name_for_dorms` varchar(64) NOT NULL default 'dorms',
  `name_for_terms` varchar(32) NOT NULL default 'semester',
  PRIMARY KEY  (`ministry_id`),
  KEY `parent_ministry_id` (`parent_ministry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='forms a tree structure owing to the parent_ministry_id field' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `ministry_people`
-- 

CREATE TABLE `ministry_people` (
  `ministry_id` int(11) NOT NULL,
  `people_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY  (`ministry_id`,`people_id`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `people`
-- 

CREATE TABLE `people` (
  `people_id` int(11) NOT NULL auto_increment,
  `first_name` varchar(32) NOT NULL,
  `middle_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL COMMENT 'nick name',
  `preferred_name` varchar(32) default NULL,
  `student_code` varchar(255) NOT NULL COMMENT 'assigned by school (id card)',
  `category_id` int(11) NOT NULL,
  `major` varchar(32) NOT NULL,
  `school_id` int(11) NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('m','f','?') NOT NULL default '?',
  `home_nation` varchar(32) NOT NULL,
  `citizenship` varchar(32) NOT NULL,
  `spouse_id` int(11) NOT NULL,
  `guest_of` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `im` varchar(64) NOT NULL,
  `url` varchar(255) NOT NULL,
  `class_of` char(4) NOT NULL,
  `selected` tinyint(4) NOT NULL,
  `receive_email` tinyint(4) NOT NULL,
  `receive_sms` tinyint(4) NOT NULL,
  `notes` mediumtext NOT NULL,
  `last_updated` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `created_on` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`people_id`),
  KEY `last_updated` (`last_updated`),
  KEY `created_on` (`created_on`),
  KEY `name` (`first_name`,`middle_name`,`last_name`,`preferred_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `roles`
-- 

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL auto_increment,
  `role` varchar(32) NOT NULL,
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `schools`
-- 

CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL,
  `url` varchar(255) NOT NULL,
  `search_url` varchar(255) NOT NULL COMMENT 'printf style string for searching school names online',
  PRIMARY KEY  (`school_id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `subgroups`
-- 

CREATE TABLE `subgroups` (
  `subgroup_id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `time` time NOT NULL,
  `location` varchar(64) NOT NULL,
  `notes` mediumtext NOT NULL,
  PRIMARY KEY  (`subgroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='for worship teams, bible studies, etc' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `subgroup_people`
-- 

CREATE TABLE `subgroup_people` (
  `subgroup_id` int(11) NOT NULL,
  `people_id` int(11) NOT NULL,
  `subgroup_role_id` int(11) NOT NULL,
  PRIMARY KEY  (`subgroup_id`,`people_id`),
  KEY `subgroup_role_id` (`subgroup_role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `subgroup_roles`
-- 

CREATE TABLE `subgroup_roles` (
  `subgroup_role_id` int(11) NOT NULL,
  `role` varchar(64) NOT NULL COMMENT 'in PHP scripts create default values using a SELECT box with an OTHER option so it can be customized',
  PRIMARY KEY  (`subgroup_role_id`),
  UNIQUE KEY `role` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bible study leader, vocalist, etc';

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `privileges` varchar(255) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`,`student_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

```