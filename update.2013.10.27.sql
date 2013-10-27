
ALTER TABLE `site_options` ADD `FullLogging` TINYINT( 1 ) NOT NULL DEFAULT '1';


-- --------------------------------------------------------

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


