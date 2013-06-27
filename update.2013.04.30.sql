 

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

