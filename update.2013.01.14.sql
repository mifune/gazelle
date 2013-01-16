
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
