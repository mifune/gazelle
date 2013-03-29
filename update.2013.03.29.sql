  
ALTER TABLE `badges` CHANGE `Type` `Type` ENUM( 'Shop', 'Single', 'Multiple', 'Unique', 'Donor') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
  

INSERT INTO `badges` ( `Type`, `Display`, `Sort`, `Cost`, `Title`, `Description`, `Image`, `Badge`, `Rank`) VALUES
( 'Donor', 1, 0, 10, 'Donor Bronze Heart', 'Awarded to our most generous donors. A bronze heart donor has donated very generously, pervs like this are who keep the site running.', 'doner-1.png', 'donor', 1),
( 'Donor', 1, 1, 25, 'Donor Silver Great Heart', 'Awarded to our most generous donors. A silver heart donor has donated very generously, pervs like this are who keep the site running.', 'doner-2.png', 'donor', 2),
( 'Donor', 1, 2, 50, 'Donor Gold Grand Heart', 'Awarded to our most generous donors. A gold heart donor has donated extremely generously, pervs like this are who keep the site running.', 'doner-3.png', 'donor', 3),
( 'Donor', 1, 3, 100, 'Donor Diamond Legendary Heart', 'Awarded to our most legendary generous donors. A diamond heart donor has donated extremely generously, pervs like this are who keep the site running.', 'doner-4.png', 'donor', 4);



--
-- Table structure for table `bitcoin_addresses`
--

DROP TABLE IF EXISTS `bitcoin_addresses`;
CREATE TABLE IF NOT EXISTS `bitcoin_addresses` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `public` varchar(64) NOT NULL,
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bitcoin_donations`
--

DROP TABLE IF EXISTS `bitcoin_donations`;
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
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Table structure for table `donation_drives`
--

DROP TABLE IF EXISTS `donation_drives`;
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






 
