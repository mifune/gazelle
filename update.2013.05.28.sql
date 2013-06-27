 
-- --------------------------------------------------------

--
-- Table structure for table `reports_conversations`
--

DROP TABLE IF EXISTS `reports_conversations`;
CREATE TABLE IF NOT EXISTS `reports_conversations` (
  `ReportID` int(11) NOT NULL,
  `ConvID` int(11) NOT NULL,
  PRIMARY KEY (`ReportID`,`ConvID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `reports_conversations` ( `ReportID` , `ConvID` )
SELECT ID, ConvID
FROM reports
WHERE ConvID !=0;



-- --------------------------------------------------------

--
-- Table structure for table `reportsv2_conversations`
--

DROP TABLE IF EXISTS `reportsv2_conversations`;
CREATE TABLE IF NOT EXISTS `reportsv2_conversations` (
  `ReportID` int(11) NOT NULL,
  `ConvID` int(11) NOT NULL,
  PRIMARY KEY (`ReportID`,`ConvID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


