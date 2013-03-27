 
ALTER TABLE `badges_auto` CHANGE `Action` `Action` ENUM( 'NumPosts', 'NumComments', 'NumUploaded', 'NumNewTags', 'NumTags', 'NumTagVotes', 'RequestsFilled', 'UploadedTB', 'DownloadedTB', 'MaxSnatches', 'NumBounties' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;



INSERT INTO `badges` (`ID`, `Type`, `Display`, `Sort`, `Cost`, `Title`, `Description`, `Image`, `Badge`, `Rank`) VALUES
(260, 'Single', 1, 80, 0, 'Bounty Hunter', 'awarded for finding 10 dupes', 'bounty-hunter1.png', 'bounty', 1),
(261, 'Single', 1, 81, 0, 'Bounty Hunter 2', 'awarded for finding 50 dupes', 'bounty-hunter2.png', 'bounty', 2),
(262, 'Single', 1, 82, 0, 'Bounty Hunter 3', 'awarded for finding 100 dupes', 'bounty-hunter3.png', 'bounty', 3);



INSERT INTO `badges_auto` ( `BadgeID`, `Action`, `Active`, `SendPM`, `Value`, `CategoryID`) VALUES
( 260, 'NumBounties', 1, 1, 10, 0),
( 261, 'NumBounties', 1, 1, 50, 0),
( 262, 'NumBounties', 1, 1, 100, 0);


ALTER TABLE `reportsv2` ADD `Credit` ENUM( '0', '1' ) NOT NULL DEFAULT '0';

UPDATE `reportsv2` SET `Credit`='1' 
WHERE Type='dupe' AND `LogMessage` LIKE '%deleted torrent for the reason: Dupe%';
