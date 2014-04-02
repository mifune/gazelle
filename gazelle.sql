-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Värd: localhost
-- Skapad: 22 mars 2012 kl 22:30
-- Serverversion: 5.1.61
-- PHP-version: 5.3.6-13ubuntu3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gazelle`
--
CREATE DATABASE IF NOT EXISTS gazelle;

USE gazelle;

-- --------------------------------------------------------

--
-- Tabellstruktur `api_applications`
--

CREATE TABLE IF NOT EXISTS `api_applications` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Token` char(32) NOT NULL,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `api_users`
--

CREATE TABLE IF NOT EXISTS `api_users` (
  `UserID` int(10) NOT NULL,
  `AppID` int(10) NOT NULL,
  `Token` char(32) NOT NULL,
  `State` enum('0','1','2') NOT NULL DEFAULT '0',
  `Time` datetime NOT NULL,
  `Access` text,
  PRIMARY KEY (`UserID`,`AppID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `articles`
--


CREATE TABLE IF NOT EXISTS `articles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Category` int(11) NOT NULL,
  `SubCat` int(4) NOT NULL DEFAULT '1',
  `TopicID` varchar(20) NOT NULL,
  `MinClass` smallint(4) NOT NULL DEFAULT '0',
  `Title` varchar(50) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TopicID` (`TopicID`),
  KEY `Category` (`Category`),
  KEY `SubCat` (`SubCat`),
  KEY `MinClass` (`MinClass`),
  FULLTEXT KEY `Description` (`Description`),
  FULLTEXT KEY `Body` (`Body`),
  FULLTEXT KEY `Title` (`Title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellstruktur `bad_passwords`
--

CREATE TABLE IF NOT EXISTS `bad_passwords` (
  `Password` char(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `badges`
--


DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Type` enum('Shop','Single','Multiple','Unique') NOT NULL,
  `Display` int(3) NOT NULL DEFAULT '0',
  `Sort` int(10) NOT NULL,
  `Cost` int(20) NOT NULL DEFAULT '0',
  `Title` varchar(64) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Badge` varchar(12) NOT NULL,
  `Rank` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `Type` (`Type`),
  KEY `Sort` (`Sort`),
  KEY `Display` (`Display`),
  KEY `Badge` (`Badge`),
  KEY `Rank` (`Rank`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `badges_auto`
--

CREATE TABLE IF NOT EXISTS `badges_auto` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `BadgeID` int(10) NOT NULL,
  `Action` enum('NumPosts','NumComments','NumUploaded','NumNewTags','NumTags','NumTagVotes','RequestsFilled','UploadedTB','DownloadedTB','MaxSnatches','NumBounties') NOT NULL,
  `Active` tinyint(1) NOT NULL DEFAULT '0',
  `SendPM` tinyint(1) NOT NULL,
  `Value` int(10) NOT NULL,
  `CategoryID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Action` (`Action`),
  KEY `Active` (`Active`),
  KEY `BadgeID` (`BadgeID`),
  KEY `SendPM` (`SendPM`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `bitcoin_addresses`
--

CREATE TABLE IF NOT EXISTS `bitcoin_addresses` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `public` varchar(64) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `public` (`public`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `bitcoin_donations`
--

CREATE TABLE IF NOT EXISTS `bitcoin_donations` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `state` enum('unused','submitted','cleared') NOT NULL DEFAULT 'unused',
  `public` varchar(64) NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userID` int(11) unsigned NOT NULL,
  `received` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bitcoin_rate` double NOT NULL DEFAULT '0',
  `amount_bitcoin` double NOT NULL DEFAULT '0',
  `amount_euro` double NOT NULL DEFAULT '0',
  `comment` varchar(256) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `public` (`public`),
  KEY `state` (`state`),
  KEY `received` (`received`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- --------------------------------------------------------

--
-- Tabellstruktur `blog`
--

CREATE TABLE IF NOT EXISTS `blog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ThreadID` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bonus_shop_actions`
--

CREATE TABLE IF NOT EXISTS `bonus_shop_actions` (
  `ID` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `Title` varchar(256) NOT NULL,
  `Description` varchar(1024) NOT NULL,
  `Action` enum('gb','givegb','givecredits','slot','title','badge','pfl','ufl') NOT NULL,
  `Value` int(10) NOT NULL DEFAULT '0',
  `Cost` int(9) unsigned NOT NULL,
  `Sort` int(6) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Sort` (`Sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 
-- --------------------------------------------------------

--
-- Tabellstruktur `bookmarks_collages`
--

CREATE TABLE IF NOT EXISTS `bookmarks_collages` (
  `UserID` int(10) NOT NULL,
  `CollageID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`),
  KEY `CollageID` (`CollageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `bookmarks_requests`
--

CREATE TABLE IF NOT EXISTS `bookmarks_requests` (
  `UserID` int(10) NOT NULL,
  `RequestID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`),
  KEY `RequestID` (`RequestID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `bookmarks_torrents`
--

CREATE TABLE IF NOT EXISTS `bookmarks_torrents` (
  `UserID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  KEY `UserID` (`UserID`),
  KEY `GroupID` (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No Description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

 
-- --------------------------------------------------------

--
-- Tabellstruktur `collages`
--

CREATE TABLE IF NOT EXISTS `collages` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL DEFAULT '',
  `Description` text NOT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Permissions` INT(4) NOT NULL DEFAULT '0', 
  `NumTorrents` int(4) NOT NULL DEFAULT '0',
  `Deleted` enum('0','1') DEFAULT '0',
  `Locked` enum('0','1') NOT NULL DEFAULT '0',
  `CategoryID` int(2) NOT NULL DEFAULT '1',
  `TagList` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `MaxGroups` int(10) NOT NULL DEFAULT '0',
  `MaxGroupsPerUser` int(10) NOT NULL DEFAULT '0',
  `Featured` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`),
  KEY `UserID` (`UserID`),
  KEY `CategoryID` (`CategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `collages_comments`
--

CREATE TABLE IF NOT EXISTS `collages_comments` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `CollageID` int(10) NOT NULL,
  `Body` mediumtext NOT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `CollageID` (`CollageID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `collages_torrents`
--

CREATE TABLE IF NOT EXISTS `collages_torrents` (
  `CollageID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Sort` int(10) NOT NULL DEFAULT '0',
  `AddedOn` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`CollageID`,`GroupID`),
  KEY `UserID` (`UserID`),
  KEY `Sort` (`Sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `comments_edits`
--

CREATE TABLE IF NOT EXISTS `comments_edits` (
  `Page` enum('forums','collages','requests','torrents') DEFAULT NULL,
  `PostID` int(10) DEFAULT NULL,
  `EditUser` int(10) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `Body` mediumtext,
  KEY `Page` (`Page`,`PostID`),
  KEY `EditUser` (`EditUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellstruktur `countries`
--



CREATE TABLE IF NOT EXISTS `countries` (
  `cc` char(2) NOT NULL,
  `country` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`cc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `donations`
--

CREATE TABLE IF NOT EXISTS `donations` (
  `UserID` int(10) NOT NULL,
  `Amount` decimal(6,2) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Time` datetime NOT NULL,
  `Currency` varchar(5) NOT NULL DEFAULT 'USD',
  KEY `UserID` (`UserID`),
  KEY `Time` (`Time`),
  KEY `Amount` (`Amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `donation_drives`
--

CREATE TABLE IF NOT EXISTS `donation_drives` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `target_euros` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `threadid` int(11) NOT NULL DEFAULT '0',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `raised_euros` int(11) NOT NULL DEFAULT '0',
  `state` enum('active','notstarted','finished') NOT NULL DEFAULT 'notstarted',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `do_not_upload`
--

CREATE TABLE IF NOT EXISTS `do_not_upload` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Comment` varchar(255) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `drives`
--

CREATE TABLE IF NOT EXISTS `drives` (
  `DriveID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) NOT NULL,
  `Offset` varchar(10) NOT NULL,
  PRIMARY KEY (`DriveID`),
  KEY `Name` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `dupe_groups`
--

CREATE TABLE IF NOT EXISTS `dupe_groups` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Comments` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `email_blacklist`
--

CREATE TABLE IF NOT EXISTS `email_blacklist` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Time` datetime NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `featured_albums`
--

CREATE TABLE IF NOT EXISTS `featured_albums` (
  `GroupID` int(10) NOT NULL DEFAULT '0',
  `ThreadID` int(10) NOT NULL DEFAULT '0',
  `Title` varchar(35) NOT NULL DEFAULT '',
  `Started` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Ended` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums`
--

CREATE TABLE IF NOT EXISTS `forums` (
  `ID` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `CategoryID` tinyint(2) NOT NULL DEFAULT '0',
  `Sort` int(6) unsigned NOT NULL,
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Description` varchar(255) DEFAULT '',
  `MinClassRead` int(4) NOT NULL DEFAULT '0',
  `MinClassWrite` int(4) NOT NULL DEFAULT '0',
  `MinClassCreate` int(4) NOT NULL DEFAULT '0',
  `NumTopics` int(10) NOT NULL DEFAULT '0',
  `NumPosts` int(10) NOT NULL DEFAULT '0',
  `LastPostID` int(10) NOT NULL DEFAULT '0',
  `LastPostAuthorID` int(10) NOT NULL DEFAULT '0',
  `LastPostTopicID` int(10) NOT NULL DEFAULT '0',
  `LastPostTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AutoLock` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `Sort` (`Sort`),
  KEY `MinClassRead` (`MinClassRead`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_categories`
--

CREATE TABLE IF NOT EXISTS `forums_categories` (
  `ID` tinyint(2) NOT NULL AUTO_INCREMENT,
  `Name` varchar(40) NOT NULL DEFAULT '',
  `Sort` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Sort` (`Sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `forums_categories`
--

INSERT INTO `forums_categories` VALUES (1,'Site',2),(5,'Community',5),(10,'Help',10),(15,'Archive',15),(16,'Testing',1);
 

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_last_read_topics`
--

CREATE TABLE IF NOT EXISTS `forums_last_read_topics` (
  `UserID` int(10) NOT NULL,
  `TopicID` int(10) NOT NULL,
  `PostID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`TopicID`),
  KEY `TopicID` (`TopicID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_polls`
--

CREATE TABLE IF NOT EXISTS `forums_polls` (
  `TopicID` int(10) unsigned NOT NULL,
  `Question` varchar(255) NOT NULL,
  `Answers` text NOT NULL,
  `Featured` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Closed` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`TopicID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_polls_votes`
--

CREATE TABLE IF NOT EXISTS `forums_polls_votes` (
  `TopicID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `Vote` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`TopicID`,`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_posts`
--

CREATE TABLE IF NOT EXISTS `forums_posts` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `TopicID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Body` mediumtext,
  `EditedUserID` int(10) DEFAULT NULL,
  `EditedTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `TopicID` (`TopicID`),
  KEY `AuthorID` (`AuthorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_specific_rules`
--

CREATE TABLE IF NOT EXISTS `forums_specific_rules` (
  `ForumID` int(6) unsigned DEFAULT NULL,
  `ThreadID` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `forums_topics`
--

CREATE TABLE IF NOT EXISTS `forums_topics` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Title` varchar(150) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `IsLocked` enum('0','1') NOT NULL DEFAULT '0',
  `IsSticky` enum('0','1') NOT NULL DEFAULT '0',
  `ForumID` int(3) NOT NULL,
  `NumPosts` int(10) NOT NULL DEFAULT '0',
  `NumViews` INT( 7 ) NOT NULL DEFAULT '0',
  `LastPostID` int(10) NOT NULL,
  `LastPostTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastPostAuthorID` int(10) NOT NULL,
  `StickyPostID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `AuthorID` (`AuthorID`),
  KEY `ForumID` (`ForumID`),
  KEY `IsSticky` (`IsSticky`),
  KEY `LastPostID` (`LastPostID`),
  KEY `Title` (`Title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `friends`
--

CREATE TABLE IF NOT EXISTS `friends` (
  `UserID` int(10) unsigned NOT NULL,
  `FriendID` int(10) unsigned NOT NULL,
  `Comment` text NOT NULL,
  `Type` enum('friends','blocked') NOT NULL,
  PRIMARY KEY (`UserID`,`FriendID`),
  KEY `Type` (`Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `full_log`
--

--
-- Table structure for table `full_log`
--

CREATE TABLE IF NOT EXISTS `full_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `time` datetime NOT NULL,
  `ip` char(15) NOT NULL,
  `ipnum` int(11) NOT NULL,
  `request` varchar(255) NOT NULL,
  `variables` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `userid` (`userid`),
  KEY `ipnum` (`ipnum`),
  KEY `variables` (`variables`(3))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `geoip_country`
--

CREATE TABLE IF NOT EXISTS `geoip_country` (
  `StartIP` int(11) unsigned NOT NULL,
  `EndIP` int(11) unsigned NOT NULL,
  `Code` varchar(2) NOT NULL,
  PRIMARY KEY (`StartIP`,`EndIP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(64) NOT NULL,
  `Comment` text NOT NULL,
  `Log` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GroupID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `AddedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `AddedBy` int(11) NOT NULL,
  `Comment` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `GroupID_2` (`GroupID`,`UserID`),
  KEY `GroupID` (`GroupID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- Tabellstruktur `group_log`
--

CREATE TABLE IF NOT EXISTS `group_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Info` mediumtext,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Hidden` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `GroupID` (`GroupID`),
  KEY `TorrentID` (`TorrentID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 

-- --------------------------------------------------------

--
-- Tabellstruktur `imagehost_whitelist`
--

CREATE TABLE IF NOT EXISTS `imagehost_whitelist` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Imagehost` varchar(255) NOT NULL,
  `Link` varchar(255) NOT NULL,
  `Comment` varchar(255) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  `Hidden` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Time` (`Time`),
  KEY `Hidden` ( `Hidden` )
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 
-- --------------------------------------------------------

--
-- Tabellstruktur `invites`
--

CREATE TABLE IF NOT EXISTS `invites` (
  `InviterID` int(10) NOT NULL DEFAULT '0',
  `InviteKey` char(32) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Expires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`InviteKey`),
  KEY `Expires` (`Expires`),
  KEY `InviterID` (`InviterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `invite_tree`
--

CREATE TABLE IF NOT EXISTS `invite_tree` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `InviterID` int(10) NOT NULL DEFAULT '0',
  `TreePosition` int(8) NOT NULL DEFAULT '1',
  `TreeID` int(10) NOT NULL DEFAULT '1',
  `TreeLevel` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`),
  KEY `InviterID` (`InviterID`),
  KEY `TreePosition` (`TreePosition`),
  KEY `TreeID` (`TreeID`),
  KEY `TreeLevel` (`TreeLevel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `ip_bans`
--

CREATE TABLE IF NOT EXISTS `ip_bans` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FromIP` int(11) unsigned NOT NULL,
  `ToIP` int(11) unsigned NOT NULL,
  `Reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `FromIP` (`FromIP`,`ToIP`),
  KEY `ToIP` (`ToIP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- Table structure for languages

CREATE TABLE IF NOT EXISTS `languages` (
  `ID` smallint(3) NOT NULL AUTO_INCREMENT,
  `language` varchar(64) NOT NULL,
  `code` char(2) NOT NULL,
  `flag_cc` char(2) DEFAULT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `library_contest`
--

CREATE TABLE IF NOT EXISTS `library_contest` (
  `UserID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Message` TEXT NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `Message` (`Message`(255)),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `login_attempts`
--

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `IP` varchar(15) NOT NULL,
  `LastAttempt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Attempts` int(10) unsigned NOT NULL,
  `BannedUntil` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Bans` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `IP` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `ocelot_query_times`
--

CREATE TABLE IF NOT EXISTS `ocelot_query_times` (
  `buffer` enum('users','torrents','snatches','peers') NOT NULL,
  `starttime` datetime NOT NULL,
  `ocelotinstance` datetime NOT NULL,
  `querylength` int(11) NOT NULL,
  `timespent` int(11) NOT NULL,
  UNIQUE KEY `starttime` (`starttime`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Level` int(10) unsigned NOT NULL,
  `Name` varchar(25) CHARACTER SET latin1 NOT NULL,
  `MaxSigLength` smallint(4) unsigned NOT NULL DEFAULT '0',
  `MaxAvatarWidth` smallint(4) unsigned NOT NULL DEFAULT '100',
  `MaxAvatarHeight` smallint(4) unsigned NOT NULL DEFAULT '100',
  `Color` char(6) CHARACTER SET utf8 NOT NULL DEFAULT '000000',
  `Values` text CHARACTER SET latin1 NOT NULL,
  `DisplayStaff` enum('0','1') CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `IsUserClass` enum( '0', '1' ) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `Level` (`Level`),
  KEY `DisplayStaff` (`DisplayStaff`),
  KEY `IsUserClass` (`IsUserClass`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 
-- --------------------------------------------------------

--
-- Tabellstruktur `pm_conversations`
--

CREATE TABLE IF NOT EXISTS `pm_conversations` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `Subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `pm_conversations_users`
--

CREATE TABLE IF NOT EXISTS `pm_conversations_users` (
  `UserID` int(10) NOT NULL DEFAULT '0',
  `ConvID` int(12) NOT NULL DEFAULT '0',
  `InInbox` enum('1','0') NOT NULL,
  `InSentbox` enum('1','0') NOT NULL,
  `SentDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ReceivedDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `UnRead` enum('1','0') NOT NULL DEFAULT '1',
  `Sticky` enum('1','0') NOT NULL DEFAULT '0',
  `ForwardedTo` int(12) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`ConvID`),
  KEY `ConvID` (`ConvID`),
  KEY `SentDate` (`SentDate`),
  KEY `ReceivedDate` (`ReceivedDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `pm_messages`
--

CREATE TABLE IF NOT EXISTS `pm_messages` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `ConvID` int(12) NOT NULL DEFAULT '0',
  `SentDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SenderID` int(10) NOT NULL DEFAULT '0',
  `Body` text,
  PRIMARY KEY (`ID`),
  KEY `ConvID` (`ConvID`),
  KEY `SenderID` (`SenderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `ThingID` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` varchar(30) DEFAULT NULL,
  `Comment` text,
  `ResolverID` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` enum('New','InProgress','Resolved') DEFAULT 'New',
  `ResolvedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ReportedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Reason` text NOT NULL,
  `ConvID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Status` (`Status`),
  KEY `Type` (`Type`),
  KEY `ResolvedTime` (`ResolvedTime`),
  KEY `ResolverID` (`ResolverID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports_conversations`
--

CREATE TABLE IF NOT EXISTS `reports_conversations` (
  `ReportID` int(11) NOT NULL,
  `ConvID` int(11) NOT NULL,
  PRIMARY KEY (`ReportID`,`ConvID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `reportsv2`
--

CREATE TABLE IF NOT EXISTS `reportsv2` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ReporterID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `Type` varchar(20) DEFAULT '',
  `UserComment` text NOT NULL,
  `ResolverID` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` enum('New','InProgress','Resolved') DEFAULT 'New',
  `ReportedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastChangeTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ModComment` text NOT NULL,
  `Track` text,
  `Image` text,
  `ExtraID` text,
  `Link` text,
  `LogMessage` text,
  `Credit` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Status` (`Status`),
  KEY `Type` (`Type`(1)),
  KEY `LastChangeTime` (`LastChangeTime`),
  KEY `ReporterID` (`ReporterID`),
  KEY `TorrentID` (`TorrentID`),
  KEY `ResolverID` (`ResolverID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reportsv2_conversations`
--

CREATE TABLE IF NOT EXISTS `reportsv2_conversations` (
  `ReportID` int(11) NOT NULL,
  `ConvID` int(11) NOT NULL,
  PRIMARY KEY (`ReportID`,`ConvID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `requests`
--

CREATE TABLE IF NOT EXISTS `requests` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` datetime NOT NULL,
  `LastVote` datetime DEFAULT NULL,
  `CategoryID` int(3) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Description` text NOT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `Paid` enum( 'Notfilled', 'Pending', 'PaidAuto', 'PaidManual' ) NOT NULL DEFAULT 'Notfilled',
  `StaffID` int( 11 ) NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `GroupID` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Userid` (`UserID`),
  KEY `Name` (`Title`),
  KEY `Filled` (`TorrentID`),
  KEY `FillerID` (`FillerID`),
  KEY `TimeAdded` (`TimeAdded`),
  KEY `TimeFilled` (`TimeFilled`),
  KEY `LastVote` (`LastVote`),
  KEY `GroupID` (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `requests_comments`
--

CREATE TABLE IF NOT EXISTS `requests_comments` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `RequestID` int(10) NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime DEFAULT NULL,
  `Body` mediumtext,
  `EditedUserID` int(10) DEFAULT NULL,
  `EditedTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `requests_tags`
--

CREATE TABLE IF NOT EXISTS `requests_tags` (
  `TagID` int(10) NOT NULL DEFAULT '0',
  `RequestID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`TagID`,`RequestID`),
  KEY `TagID` (`TagID`),
  KEY `RequestID` (`RequestID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `requests_votes`
--

CREATE TABLE IF NOT EXISTS `requests_votes` (
  `RequestID` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL DEFAULT '0',
  `Bounty` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`RequestID`,`UserID`),
  KEY `RequestID` (`RequestID`),
  KEY `UserID` (`UserID`),
  KEY `Bounty` (`Bounty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




-- --------------------------------------------------------

--
-- Table structure for table `review_reasons`
--

CREATE TABLE IF NOT EXISTS `review_reasons` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Sort` int(5) NOT NULL DEFAULT '0',
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Sort` (`Sort`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `review_reasons`
--

INSERT INTO `review_reasons` VALUES (1,2,'Screenshots','Not enough screenshots.'),(2,4,'Description','Lack of text description.'),(3,8,'Screenshots & Description','Not enough screenshots, lack of text description.');


-- --------------------------------------------------------

--
-- Tabellstruktur `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `NextHour` int(2) NOT NULL DEFAULT '0',
  `NextDay` int(2) NOT NULL DEFAULT '0',
  `NextBiWeekly` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` VALUES (15,29,8);



-- --------------------------------------------------------

--
-- Table structure for table `site_options`
--

CREATE TABLE IF NOT EXISTS `site_options` (
  `ReviewHours` int(4) NOT NULL,
  `AutoDelete` tinyint(1) NOT NULL,
  `DeleteRecordsMins` int(8) NOT NULL,
  `KeepSpeed` bigint(11) NOT NULL,
  `FreeLeech` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `site_options`
--
 
INSERT INTO `site_options` VALUES (24,0,720,524288,'0000-00-00 00:00:00');


-- --------------------------------------------------------

--
-- Table structure for table `site_stats_history`
--

CREATE TABLE IF NOT EXISTS `site_stats_history` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TimeAdded` datetime NOT NULL,
  `Users` int(10) unsigned NOT NULL,
  `Torrents` int(10) unsigned NOT NULL,
  `Seeders` int(10) unsigned NOT NULL,
  `Leechers` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `TimeAdded` (`TimeAdded`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sm_results`
--

CREATE TABLE IF NOT EXISTS `sm_results` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Spins` smallint(1) NOT NULL,
  `Won` int(11) NOT NULL,
  `Bet` mediumint(5) NOT NULL,
  `Result` varchar(12) CHARACTER SET utf8 NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `Won` (`Won`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `sphinx_delta`
--

CREATE TABLE IF NOT EXISTS `sphinx_delta` (
  `ID` int(10) NOT NULL,
  `GroupName` varchar(255) DEFAULT NULL,
  `TagList` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NewCategoryID` int(11) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Time` int(12) DEFAULT NULL,
  `Size` bigint(20) DEFAULT NULL,
  `Snatched` int(10) DEFAULT NULL,
  `Seeders` int(10) DEFAULT NULL,
  `Leechers` int(10) DEFAULT NULL,
  `FreeTorrent` tinyint(1) DEFAULT NULL,
  `FileList` mediumtext,
  `SearchText` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellstruktur `sphinx_hash`
--

CREATE TABLE IF NOT EXISTS `sphinx_hash` (
  `ID` int(10) NOT NULL,
  `GroupName` varchar(255) DEFAULT NULL,
  `TagList` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NewCategoryID` int(11) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Time` int(12) DEFAULT NULL,
  `Size` bigint(20) DEFAULT NULL,
  `Snatched` int(10) DEFAULT NULL,
  `Seeders` int(10) DEFAULT NULL,
  `Leechers` int(10) DEFAULT NULL,
  `FreeTorrent` tinyint(1) DEFAULT NULL,
  `FileList` mediumtext,
  `SearchText` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `sphinx_requests`
--

CREATE TABLE IF NOT EXISTS `sphinx_requests` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` int(12) unsigned NOT NULL,
  `LastVote` int(12) unsigned NOT NULL,
  `CategoryID` int(3) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` int(12) unsigned NOT NULL,
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `Bounty` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Votes` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Userid` (`UserID`),
  KEY `Name` (`Title`),
  KEY `Filled` (`TorrentID`),
  KEY `FillerID` (`FillerID`),
  KEY `TimeAdded` (`TimeAdded`),
  KEY `TimeFilled` (`TimeFilled`),
  KEY `LastVote` (`LastVote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `sphinx_requests_delta`
--

CREATE TABLE IF NOT EXISTS `sphinx_requests_delta` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeAdded` int(12) unsigned DEFAULT NULL,
  `LastVote` int(12) unsigned DEFAULT NULL,
  `CategoryID` int(3) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT NULL,
  `FillerID` int(10) unsigned NOT NULL DEFAULT '0',
  `TorrentID` int(10) unsigned NOT NULL DEFAULT '0',
  `TimeFilled` int(12) unsigned DEFAULT NULL,
  `Visible` binary(1) NOT NULL DEFAULT '1',
  `Bounty` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Votes` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Userid` (`UserID`),
  KEY `Name` (`Title`),
  KEY `Filled` (`TorrentID`),
  KEY `FillerID` (`FillerID`),
  KEY `TimeAdded` (`TimeAdded`),
  KEY `TimeFilled` (`TimeFilled`),
  KEY `LastVote` (`LastVote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `staff_blog`
--

CREATE TABLE IF NOT EXISTS `staff_blog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Body` text NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `staff_blog_visits`
--

CREATE TABLE IF NOT EXISTS `staff_blog_visits` (
  `UserID` int(10) unsigned NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--
-- Table structure for table `staff_checking`
--

CREATE TABLE IF NOT EXISTS `staff_checking` (
  `UserID` int(10) unsigned NOT NULL,
  `TimeOut` int(10) unsigned NOT NULL,
  `TimeStarted` datetime NOT NULL,
  `Location` varchar(256) NOT NULL,
  `IsChecking` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`),
  KEY ( `IsChecking` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellstruktur `staff_pm_conversations`
--


CREATE TABLE IF NOT EXISTS `staff_pm_conversations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Subject` text,
  `UserID` int(11) DEFAULT NULL,
  `Status` enum('Open','Unanswered','Resolved') DEFAULT NULL,
  `Level` int(11) DEFAULT NULL,
  `AssignedToUser` int(11) DEFAULT NULL,
  `Date` datetime DEFAULT NULL,
  `Unread` tinyint(1) DEFAULT NULL,
  `ResolverID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `staff_pm_messages`
--

CREATE TABLE IF NOT EXISTS `staff_pm_messages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `SentDate` datetime DEFAULT NULL,
  `Message` text,
  `ConvID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `staff_pm_responses`
--

CREATE TABLE IF NOT EXISTS `staff_pm_responses` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Message` text,
  `Name` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `stylesheets`
--

CREATE TABLE IF NOT EXISTS `stylesheets` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Description` varchar(255) NOT NULL,
  `Default` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 


-- --------------------------------------------------------

--
-- Tabellstruktur `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `TagType` enum('genre','other') NOT NULL DEFAULT 'other',
  `Uses` int(12) NOT NULL DEFAULT '1',
  `UserID` int(10) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name_2` (`Name`),
  KEY `TagType` (`TagType`),
  KEY `Uses` (`Uses`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tag_synomyns`
--

CREATE TABLE IF NOT EXISTS `tag_synomyns` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Synomyn` varchar(100) NOT NULL,
  `TagID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Synomyn` (`Synomyn`),
  KEY `TagID` (`TagID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `top10_history`
--

CREATE TABLE IF NOT EXISTS `top10_history` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Type` enum('Daily','Weekly') DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `top10_history_torrents`
--

CREATE TABLE IF NOT EXISTS `top10_history_torrents` (
  `HistoryID` int(10) NOT NULL DEFAULT '0',
  `Rank` tinyint(2) NOT NULL DEFAULT '0',
  `TorrentID` int(10) NOT NULL DEFAULT '0',
  `TitleString` varchar(150) NOT NULL DEFAULT '',
  `TagString` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `top_snatchers`
--

CREATE TABLE IF NOT EXISTS `top_snatchers` (
  `UserID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) DEFAULT NULL,
  `info_hash` blob NOT NULL,
  `InfoHash` char(40) NOT NULL DEFAULT '',
  `FileCount` int(6) NOT NULL,
  `FileList` mediumtext NOT NULL,
  `FilePath` varchar(255) NOT NULL DEFAULT '',
  `Size` bigint(12) NOT NULL,
  `Leechers` int(6) NOT NULL DEFAULT '0',
  `Seeders` int(6) NOT NULL DEFAULT '0',
  `last_action` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `FreeTorrent` enum('0','1','2') NOT NULL DEFAULT '0',
  `FreeLeechType` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `double_seed` enum('0','1') NOT NULL DEFAULT '0',
  `Dupable` enum('0','1') NOT NULL DEFAULT '0',
  `DupeReason` varchar(40) DEFAULT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Anonymous` enum('0','1') NOT NULL DEFAULT '0',
  `Thanks` text NOT NULL,
  `Snatched` int(10) unsigned NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL,
  `announced_http` int(11) NOT NULL,
  `announced_http_compact` int(11) NOT NULL,
  `announced_http_no_peer_id` int(11) NOT NULL,
  `announced_udp` int(11) NOT NULL,
  `scraped_http` int(11) NOT NULL,
  `scraped_udp` int(11) NOT NULL,
  `started` int(11) NOT NULL,
  `stopped` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `balance` bigint(20) NOT NULL DEFAULT '0',
  `LastLogged` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pid` int(5) NOT NULL DEFAULT '0',
  `LastReseedRequest` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ExtendedGrace` enum('0','1') NOT NULL DEFAULT '0',
  `Tasted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `InfoHash` (`info_hash`(40)),
  KEY `GroupID` (`GroupID`),
  KEY `UserID` (`UserID`),
  KEY `FileCount` (`FileCount`),
  KEY `Size` (`Size`),
  KEY `Seeders` (`Seeders`),
  KEY `Leechers` (`Leechers`),
  KEY `Snatched` (`Snatched`),
  KEY `last_action` (`last_action`),
  KEY `Time` (`Time`),
  KEY `flags` (`flags`),
  KEY `LastLogged` (`LastLogged`),
  KEY `FreeTorrent` (`FreeTorrent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_bad_files`
--

CREATE TABLE IF NOT EXISTS `torrents_bad_files` (
  `TorrentID` int(11) NOT NULL DEFAULT '0',
  `UserID` int(11) NOT NULL DEFAULT '0',
  `TimeAdded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_bad_folders`
--

CREATE TABLE IF NOT EXISTS `torrents_bad_folders` (
  `TorrentID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `TimeAdded` datetime NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_bad_tags`
--

CREATE TABLE IF NOT EXISTS `torrents_bad_tags` (
  `TorrentID` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL DEFAULT '0',
  `TimeAdded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `TimeAdded` (`TimeAdded`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_balance_history`
--

CREATE TABLE IF NOT EXISTS `torrents_balance_history` (
  `TorrentID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `Time` datetime NOT NULL,
  `Last` enum('0','1','2') DEFAULT '0',
  UNIQUE KEY `TorrentID_2` (`TorrentID`,`Time`),
  UNIQUE KEY `TorrentID_3` (`TorrentID`,`balance`),
  KEY `TorrentID` (`TorrentID`),
  KEY `Time` (`Time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_comments`
--

CREATE TABLE IF NOT EXISTS `torrents_comments` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) unsigned NOT NULL,
  `AuthorID` int(10) NOT NULL,
  `AddedTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Body` mediumtext,
  `EditedUserID` int(10) DEFAULT NULL,
  `EditedTime` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `TopicID` (`GroupID`),
  KEY `AuthorID` (`AuthorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_files`
--

CREATE TABLE IF NOT EXISTS `torrents_files` (
  `TorrentID` int(10) NOT NULL,
  `File` mediumblob NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- Table structure for table `torrents_files_temp`
--

DROP TABLE IF EXISTS `torrents_files_temp`;
CREATE TABLE IF NOT EXISTS `torrents_files_temp` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file` mediumblob NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_group`
--

CREATE TABLE IF NOT EXISTS `torrents_group` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `NewCategoryID` int(11) NOT NULL,
  `Name` varchar(300) DEFAULT NULL,
  `ReleaseType` tinyint(2) DEFAULT '21',
  `TagList` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Body` mediumtext NOT NULL,
  `Image` varchar(255) NOT NULL,
  `SearchText` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `NewCategoryID` (`NewCategoryID`),
  KEY `Name` (`Name`(255)),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_groups_log`
--

CREATE TABLE IF NOT EXISTS `torrents_groups_log` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL DEFAULT '0',
  `UserID` int(10) NOT NULL,
  `Info` mediumtext,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Hidden` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `TorrentID` (`TorrentID`),
  KEY `GroupID` (`GroupID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_logs_new`
--

CREATE TABLE IF NOT EXISTS `torrents_logs_new` (
  `LogID` int(10) NOT NULL AUTO_INCREMENT,
  `TorrentID` int(10) NOT NULL DEFAULT '0',
  `Log` mediumtext NOT NULL,
  `Details` mediumtext NOT NULL,
  `Score` int(3) NOT NULL,
  `Revision` int(3) NOT NULL,
  `Adjusted` enum('1','0') NOT NULL DEFAULT '0',
  `AdjustedBy` int(10) NOT NULL DEFAULT '0',
  `NotEnglish` enum('1','0') NOT NULL DEFAULT '0',
  `AdjustmentReason` text,
  PRIMARY KEY (`LogID`),
  KEY `TorrentID` (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_peerlists`
--

CREATE TABLE IF NOT EXISTS `torrents_peerlists` (
  `GroupID` int(10) NOT NULL,
  `SeedersList` varchar(512) DEFAULT NULL,
  `LeechersList` varchar(512) DEFAULT NULL,
  `SnatchedList` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_peerlists_compare`
--

CREATE TABLE IF NOT EXISTS `torrents_peerlists_compare` (
  `GroupID` int(10) NOT NULL,
  `SeedersList` varchar(512) DEFAULT NULL,
  `LeechersList` varchar(512) DEFAULT NULL,
  `SnatchedList` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_recommended`
--

CREATE TABLE IF NOT EXISTS `torrents_recommended` (
  `GroupID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`GroupID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `torrents_reviews`
--

CREATE TABLE IF NOT EXISTS `torrents_reviews` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `GroupID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ReasonID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `ConvID` int(10) DEFAULT NULL,
  `Status` enum('None','Okay','Warned','Pending') NOT NULL DEFAULT 'None',
  `Reason` varchar(255) DEFAULT NULL,
  `KillTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID`),
  KEY `GroupID` (`GroupID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_tags`
--

CREATE TABLE IF NOT EXISTS `torrents_tags` (
  `TagID` int(10) NOT NULL DEFAULT '0',
  `GroupID` int(10) NOT NULL DEFAULT '0',
  `PositiveVotes` int(6) NOT NULL DEFAULT '1',
  `NegativeVotes` int(6) NOT NULL DEFAULT '1',
  `UserID` int(10) DEFAULT NULL,
  PRIMARY KEY (`TagID`,`GroupID`),
  KEY `TagID` (`TagID`),
  KEY `GroupID` (`GroupID`),
  KEY `PositiveVotes` (`PositiveVotes`),
  KEY `NegativeVotes` (`NegativeVotes`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `torrents_tags_votes`
--

CREATE TABLE IF NOT EXISTS `torrents_tags_votes` (
  `GroupID` int(10) NOT NULL,
  `TagID` int(10) NOT NULL,
  `UserID` int(10) NOT NULL,
  `Way` enum('up','down') NOT NULL DEFAULT 'up',
  PRIMARY KEY (`GroupID`,`TagID`,`UserID`,`Way`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `torrents_watch_list`
--

CREATE TABLE IF NOT EXISTS `torrents_watch_list` (
  `TorrentID` int(10) NOT NULL,
  `StaffID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Comment` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `upload_templates`
--

CREATE TABLE IF NOT EXISTS `upload_templates` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserID` int(10) unsigned NOT NULL,
  `TimeAdded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Name` varchar(64) NOT NULL,
  `Public` enum('0','1') NOT NULL DEFAULT '0',
  `Title` varchar(255) NOT NULL,
  `Image` varchar(255) NOT NULL,
  `Body` mediumtext NOT NULL,
  `CategoryID` int(10) NOT NULL,
  `Taglist` text NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `TimeAdded` (`TimeAdded`),
  KEY `Public` (`Public`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_badges`
--

CREATE TABLE IF NOT EXISTS `users_badges` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `BadgeID` int(10) NOT NULL,
  `Description` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`),
  KEY `BadgeID` (`BadgeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_collage_subs`
--

CREATE TABLE IF NOT EXISTS `users_collage_subs` (
  `UserID` int(10) NOT NULL,
  `CollageID` int(10) NOT NULL,
  `LastVisit` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`,`CollageID`),
  KEY `CollageID` (`CollageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `users_connectable_status`
--

CREATE TABLE IF NOT EXISTS `users_connectable_status` (
 `UserID` int(10) unsigned NOT NULL,
 `IP` varchar(15) NOT NULL DEFAULT '',
 `Status` enum('no','yes','unset') NOT NULL DEFAULT 'unset',
 `Time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`IP`),
  KEY `Status` (`Status`) ,
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellstruktur `users_downloads`
--

CREATE TABLE IF NOT EXISTS `users_downloads` (
  `UserID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY (`UserID`,`TorrentID`),
  KEY `TorrentID` (`TorrentID`),
  KEY `Time` (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_dupes`
--

CREATE TABLE IF NOT EXISTS `users_dupes` (
  `GroupID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  UNIQUE KEY `UserID` (`UserID`),
  KEY `GroupID` (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- table structure `users_freeleeches`
--
 
CREATE TABLE IF NOT EXISTS `users_freeleeches` (
  `UserID` int(11) NOT NULL,
  `TorrentID` int(11) NOT NULL,
  `Downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_geodistribution`
--

CREATE TABLE IF NOT EXISTS `users_geodistribution` (
  `Code` char(2) NOT NULL,
  `Users` int(10) NOT NULL,
  PRIMARY KEY (`Code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_history_emails`
--

CREATE TABLE IF NOT EXISTS `users_history_emails` (
  `UserID` int(10) NOT NULL,
  `Email` varchar(255) NOT NULL DEFAULT '',
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IP` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `ChangedbyID` int(10) NOT NULL,
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_history_ips`
--

CREATE TABLE IF NOT EXISTS `users_history_ips` (
  `UserID` int(10) NOT NULL,
  `IP` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `StartTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `EndTime` datetime DEFAULT NULL,
  PRIMARY KEY (`UserID`,`IP`,`StartTime`),
  KEY `UserID` (`UserID`),
  KEY `IP` (`IP`),
  KEY `StartTime` (`StartTime`),
  KEY `EndTime` (`EndTime`),
  KEY `IP_2` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_history_passkeys`
--

CREATE TABLE IF NOT EXISTS `users_history_passkeys` (
  `UserID` int(10) NOT NULL,
  `OldPassKey` varchar(32) DEFAULT NULL,
  `NewPassKey` varchar(32) DEFAULT NULL,
  `ChangeTime` datetime DEFAULT NULL,
  `ChangerIP` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_history_passwords`
--

CREATE TABLE IF NOT EXISTS `users_history_passwords` (
  `UserID` int(10) NOT NULL,
  `ChangeTime` datetime DEFAULT NULL,
  `ChangerIP` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_info`
--

CREATE TABLE IF NOT EXISTS `users_info` (
  `UserID` int(10) unsigned NOT NULL,
  `StyleID` int(10) unsigned NOT NULL,
  `StyleURL` varchar(255) DEFAULT NULL,
  `Info` text NOT NULL,
  `Avatar` varchar(255) NOT NULL,
  `Country` int(10) unsigned NOT NULL,
  `TimeZone` VARCHAR( 128 ) NOT NULL,
  `AdminComment` mediumtext NOT NULL,
  `SeedHistory` mediumtext NOT NULL,
  `SiteOptions` text NOT NULL,
  `ViewAvatars` enum('0','1') NOT NULL DEFAULT '1',
  `Donor` enum('0','1') NOT NULL DEFAULT '0',
  `DownloadAlt` enum('0','1') NOT NULL DEFAULT '0',
  `Warned` datetime NOT NULL,
  `MessagesPerPage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `DeletePMs` enum('0','1') NOT NULL DEFAULT '1',
  `SaveSentPMs` enum('0','1') NOT NULL DEFAULT '0',
  `BlockPMs` enum('0','1','2') NOT NULL DEFAULT '0', 
  `CommentsNotify` enum('0','1') NOT NULL DEFAULT '1', 
  `SupportFor` varchar(255) NOT NULL,
  `TorrentGrouping` enum('0','1','2') NOT NULL COMMENT '0=Open,1=Closed,2=Off',
  `ShowTags` enum('0','1') NOT NULL DEFAULT '1',
  `AuthKey` varchar(32) NOT NULL,
  `ResetKey` varchar(32) NOT NULL,
  `ResetExpires` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `JoinDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Inviter` int(10) DEFAULT NULL,
  `BitcoinAddress` tinytext,
  `WarnedTimes` int(2) NOT NULL DEFAULT '0',
  `SuppressConnPrompt` enum('0','1') NOT NULL DEFAULT '0',
  `DisableAvatar` enum('0','1') NOT NULL DEFAULT '0',
  `DisableInvites` enum('0','1') NOT NULL DEFAULT '0',
  `DisablePosting` enum('0','1') NOT NULL DEFAULT '0',
  `DisableForums` enum('0','1') NOT NULL DEFAULT '0',
  `DisableIRC` enum('0','1') NOT NULL DEFAULT '0',
  `DisableTagging` enum('0','1') NOT NULL DEFAULT '0',
  `DisableUpload` enum('0','1') NOT NULL DEFAULT '0',
  `DisablePM` enum('0','1') NOT NULL DEFAULT '0',
  `DisableSignature` enum('0','1') NOT NULL DEFAULT '0',
  `DisableTorrentSig` enum('0','1') NOT NULL DEFAULT '0',
  `RatioWatchEnds` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `RatioWatchDownload` bigint(20) unsigned NOT NULL DEFAULT '0',
  `RatioWatchTimes` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `BanDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `BanReason` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `CatchupTime` datetime DEFAULT NULL,
  `LastReadNews` int(10) NOT NULL DEFAULT '0',
  `HideCountryChanges` enum('0','1') NOT NULL DEFAULT '0',
  `RestrictedForums` varchar(150) NOT NULL DEFAULT '',
  `DisableRequests` enum('0','1') NOT NULL DEFAULT '0',
  `PermittedForums` varchar(150) NOT NULL DEFAULT '',
  `UnseededAlerts` enum('0','1') NOT NULL DEFAULT '0',
  `BonusLog` mediumtext NOT NULL,
  `TorrentSignature` text NOT NULL,
  `RunHour` tinyint(2) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `UserID` (`UserID`),
  KEY `SupportFor` (`SupportFor`),
  KEY `DisableInvites` (`DisableInvites`),
  KEY `Donor` (`Donor`),
  KEY `Warned` (`Warned`),
  KEY `JoinDate` (`JoinDate`),
  KEY `Inviter` (`Inviter`),
  KEY `RatioWatchEnds` (`RatioWatchEnds`),
  KEY `RatioWatchDownload` (`RatioWatchDownload`),
  KEY `RunHour` (`RunHour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_languages`
--

CREATE TABLE IF NOT EXISTS `users_languages` (
  `UserID` int(10) NOT NULL,
  `LangID` smallint(3) NOT NULL,
  PRIMARY KEY (`UserID`,`LangID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_main`
--

CREATE TABLE IF NOT EXISTS `users_main` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(30) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `PassHash` char(40) NOT NULL,
  `Secret` char(32) NOT NULL,
  `TorrentKey` char(32) NOT NULL,
  `IRCKey` char(32) DEFAULT NULL,
  `LastLogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `LastAccess` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IP` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `Uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Downloaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `UploadedDaily` bigint(20) unsigned NOT NULL DEFAULT '0',
  `DownloadedDaily` bigint(20) unsigned NOT NULL DEFAULT '0',
  `Title` varchar(128) NOT NULL,
  `Enabled` enum('0','1','2') NOT NULL DEFAULT '0',
  `Paranoia` text,
  `Visible` enum('1','0') NOT NULL DEFAULT '1',
  `Invites` int(10) unsigned NOT NULL DEFAULT '0',
  `PermissionID` int(10) unsigned NOT NULL,
  `GroupPermissionID` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0' ,
  `CustomPermissions` text,
  `LastSeed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `wait_time` int(11) NOT NULL,
  `peers_limit` int(11) DEFAULT '1000',
  `torrents_limit` int(11) DEFAULT '1000',
  `torrent_pass` char(32) NOT NULL,
  `OldPassHash` char(32) DEFAULT NULL,
  `Cursed` enum('1','0') NOT NULL DEFAULT '0',
  `CookieID` varchar(32) DEFAULT NULL,
  `RequiredRatio` double(10,8) NOT NULL DEFAULT '0.00000000',
  `RequiredRatioWork` double(10,8) NOT NULL DEFAULT '0.00000000',
  `Language` char(2) NOT NULL DEFAULT '',
  `ipcc` char(2) NOT NULL DEFAULT '',
  `FLTokens` int(10) NOT NULL DEFAULT '0',
  `personal_freeleech` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SeedHoursDaily` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00',
  `SeedHours` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00',
  `CreditsDaily` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00',
  `Credits` double(11,2) NOT NULL DEFAULT '0.00',
  `Signature` text,
  `Flag` VARCHAR( 50 ) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Username` (`Username`),
  KEY `Email` (`Email`),
  KEY `PassHash` (`PassHash`),
  KEY `LastAccess` (`LastAccess`),
  KEY `IP` (`IP`),
  KEY `Uploaded` (`Uploaded`),
  KEY `Downloaded` (`Downloaded`),
  KEY `Enabled` (`Enabled`),
  KEY `Invites` (`Invites`),
  KEY `torrent_pass` (`torrent_pass`),
  KEY `RequiredRatio` (`RequiredRatio`),
  KEY `SeedHoursDaily` (`SeedHoursDaily`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_not_cheats`
--

DROP TABLE IF EXISTS `users_not_cheats`;
CREATE TABLE IF NOT EXISTS `users_not_cheats` (
  `UserID` int(10) NOT NULL,
  `StaffID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Comment` varchar(255) NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_notify_filters`
--

CREATE TABLE IF NOT EXISTS `users_notify_filters` (
  `ID` int(12) NOT NULL AUTO_INCREMENT,
  `UserID` int(10) NOT NULL,
  `Label` varchar(128) NOT NULL DEFAULT '',
  `Users` mediumtext NOT NULL,
  `Tags` varchar(500) NOT NULL DEFAULT '',
  `NotTags` varchar(500) NOT NULL DEFAULT '',
  `Categories` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_notify_torrents`
--

CREATE TABLE IF NOT EXISTS `users_notify_torrents` (
  `UserID` int(10) NOT NULL,
  `FilterID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `TorrentID` int(10) NOT NULL,
  `UnRead` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`TorrentID`),
  KEY `UnRead` (`UnRead`),
  KEY `UserID` (`UserID`),
  KEY `TorrentID` (`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_points`
--

CREATE TABLE IF NOT EXISTS `users_points` (
  `UserID` int(10) NOT NULL,
  `GroupID` int(10) NOT NULL,
  `Points` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`GroupID`),
  KEY `UserID` (`UserID`),
  KEY `GroupID` (`GroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_points_requests`
--

CREATE TABLE IF NOT EXISTS `users_points_requests` (
  `UserID` int(10) NOT NULL,
  `RequestID` int(10) NOT NULL,
  `Points` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`RequestID`),
  KEY `UserID` (`UserID`),
  KEY `RequestID` (`RequestID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `users_seedhours_history`
--

CREATE TABLE IF NOT EXISTS `users_seedhours_history` (
  `UserID` int(10) NOT NULL,
  `Time` date NOT NULL DEFAULT '0000-00-00',
  `TimeAdded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SeedHours` double(11,2) NOT NULL DEFAULT '0.00',
  `Credits` double(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`UserID`,`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Tabellstruktur `users_sessions`
--

CREATE TABLE IF NOT EXISTS `users_sessions` (
  `UserID` int(10) NOT NULL,
  `SessionID` char(32) NOT NULL,
  `KeepLogged` enum('0','1') NOT NULL DEFAULT '0',
  `Browser` varchar(40) DEFAULT NULL,
  `OperatingSystem` varchar(8) DEFAULT NULL,
  `IP` varchar(15) NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `Active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`,`SessionID`),
  KEY `UserID` (`UserID`),
  KEY `LastUpdate` (`LastUpdate`),
  KEY `Active` (`Active`),
  KEY `ActiveAgeKeep` (`Active`,`LastUpdate`,`KeepLogged`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_slots`
--


CREATE TABLE IF NOT EXISTS `users_slots` (
  `UserID` int(11) NOT NULL,
  `TorrentID` int(11) NOT NULL,
  `FreeLeech` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DoubleSeed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`UserID`,`TorrentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_subscriptions`
--

CREATE TABLE IF NOT EXISTS `users_subscriptions` (
  `UserID` int(10) NOT NULL,
  `TopicID` int(10) NOT NULL,
  PRIMARY KEY (`UserID`,`TopicID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_torrent_history`
--

CREATE TABLE IF NOT EXISTS `users_torrent_history` (
  `UserID` int(10) unsigned NOT NULL,
  `NumTorrents` int(6) unsigned NOT NULL,
  `Date` int(8) unsigned NOT NULL,
  `Time` int(11) unsigned NOT NULL DEFAULT '0',
  `LastTime` int(11) unsigned NOT NULL DEFAULT '0',
  `Finished` enum('1','0') NOT NULL DEFAULT '1',
  `Weight` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`,`NumTorrents`,`Date`),
  KEY `Finished` (`Finished`),
  KEY `Date` (`Date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_torrent_history_snatch`
--

CREATE TABLE IF NOT EXISTS `users_torrent_history_snatch` (
  `UserID` int(10) unsigned NOT NULL,
  `NumSnatches` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`),
  KEY `NumSnatches` (`NumSnatches`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `users_torrent_history_temp`
--

CREATE TABLE IF NOT EXISTS `users_torrent_history_temp` (
  `UserID` int(10) unsigned NOT NULL,
  `NumTorrents` int(6) unsigned NOT NULL DEFAULT '0',
  `SumTime` bigint(20) unsigned NOT NULL DEFAULT '0',
  `SeedingAvg` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_watch_list`
--

CREATE TABLE IF NOT EXISTS `users_watch_list` (
  `UserID` int(10) NOT NULL,
  `StaffID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Comment` varchar(255) CHARACTER SET utf8 NOT NULL,
  `KeepTorrents` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `users_exclude_watchlist`
--

CREATE TABLE IF NOT EXISTS `users_exclude_watchlist` (
  `UserID` int(10) NOT NULL,
  `StaffID` int(10) NOT NULL,
  `Time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Comment` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_announce_log`
--

CREATE TABLE IF NOT EXISTS `xbt_announce_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(10) unsigned NOT NULL,
  `port` int(11) NOT NULL,
  `event` int(11) NOT NULL,
  `info_hash` blob NOT NULL,
  `peer_id` blob NOT NULL,
  `downloaded` bigint(20) NOT NULL,
  `left0` bigint(20) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `uid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `useragent` varchar(51) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_cheat`
--

CREATE TABLE IF NOT EXISTS `xbt_cheat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `ipa` int(10) unsigned NOT NULL,
  `upspeed` bigint(20) NOT NULL,
  `tstamp` int(11) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_client_blacklist`
--

CREATE TABLE IF NOT EXISTS `xbt_client_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `peer_id` varchar(20) DEFAULT NULL,
  `vstring` varchar(200) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `peer_id` (`peer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_config`
--

CREATE TABLE IF NOT EXISTS `xbt_config` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_deny_from_hosts`
--

CREATE TABLE IF NOT EXISTS `xbt_deny_from_hosts` (
  `begin` int(11) NOT NULL,
  `end` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_files`
--

CREATE TABLE IF NOT EXISTS `xbt_files` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `info_hash` blob NOT NULL,
  `leechers` int(11) NOT NULL,
  `seeders` int(11) NOT NULL,
  `completed` int(11) NOT NULL,
  `announced_http` int(11) NOT NULL,
  `announced_http_compact` int(11) NOT NULL,
  `announced_http_no_peer_id` int(11) NOT NULL,
  `announced_udp` int(11) NOT NULL,
  `scraped_http` int(11) NOT NULL,
  `scraped_udp` int(11) NOT NULL,
  `started` int(11) NOT NULL,
  `stopped` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `balance` int(11) NOT NULL,
  `freetorrent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `info_hash` (`info_hash`(20))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_files_users`
--

CREATE TABLE IF NOT EXISTS `xbt_files_users` (
  `uid` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `announced` int(11) NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `downloaded` bigint(20) NOT NULL,
  `remaining` bigint(20) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `upspeed` bigint(20) NOT NULL,
  `downspeed` bigint(20) NOT NULL,
  `corrupt` bigint(20) NOT NULL DEFAULT '0',
  `timespent` bigint(20) NOT NULL,
  `useragent` varchar(51) NOT NULL,
  `connectable` tinyint(4) NOT NULL DEFAULT '1',
  `peer_id` binary(20) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `fid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `port` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`peer_id`,`fid`),
  KEY `remaining_idx` (`remaining`),
  KEY `fid_idx` (`fid`),
  KEY `mtime_idx` (`mtime`),
  KEY `uid_active` (`uid`,`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure `xbt_peers_history`
--

CREATE TABLE `xbt_peers_history` (  
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `downloaded` bigint(20) NOT NULL,
  `remaining` bigint(20) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `upspeed` bigint(20) NOT NULL,
  `downspeed` bigint(20) NOT NULL,
  `timespent` bigint(20) NOT NULL,
  `peer_id` binary(20) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `fid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_scrape_log`
--

CREATE TABLE IF NOT EXISTS `xbt_scrape_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipa` int(11) NOT NULL,
  `info_hash` blob,
  `uid` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_snatched`
--

CREATE TABLE IF NOT EXISTS `xbt_snatched` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `IP` varchar(15) NOT NULL,
  KEY `fid` (`fid`),
  KEY `uid` (`uid`),
  KEY `tstamp` (`tstamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellstruktur `xbt_users`
--

CREATE TABLE IF NOT EXISTS `xbt_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(8) NOT NULL,
  `pass` blob NOT NULL,
  `can_leech` tinyint(4) NOT NULL DEFAULT '1',
  `wait_time` int(11) NOT NULL,
  `peers_limit` int(11) NOT NULL,
  `torrents_limit` int(11) NOT NULL,
  `torrent_pass` char(32) NOT NULL,
  `torrent_pass_secret` bigint(20) NOT NULL,
  `downloaded` bigint(20) NOT NULL,
  `uploaded` bigint(20) NOT NULL,
  `fid_end` int(11) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `staff_blog_visits`
--
ALTER TABLE `staff_blog_visits`
  ADD CONSTRAINT `staff_blog_visits_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE;

--
-- Restriktioner för tabell `users_dupes`
--
ALTER TABLE `users_dupes`
  ADD CONSTRAINT `users_dupes_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users_main` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_dupes_ibfk_2` FOREIGN KEY (`GroupID`) REFERENCES `dupe_groups` (`ID`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
