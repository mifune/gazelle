 
-- extend ip_bans table 

-- --------------------------------------------------------

ALTER TABLE `ip_bans` ADD `UserID` INT( 11 ) NOT NULL DEFAULT '0' AFTER `ToIP` ,
ADD `StaffID` INT( 11 ) NOT NULL DEFAULT '0' AFTER `UserID` ,
ADD `Endtime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `StaffID` ,
ADD INDEX ( `Endtime` ) 
