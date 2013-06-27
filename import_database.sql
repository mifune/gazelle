-- fetch data from emp.users into gazelle.users_main
 
insert into `gazelle`.`users_main` (`ID`, `Username`, `Email`, `PassHash`, `Secret`, `Title`, `PermissionID`, `Enabled`, `Uploaded`, `Downloaded`, `LastLogin`, `LastAccess`, `IP`, `torrent_pass`, `Credits`, `FLTokens`, `Flag`)
SELECT `eu`.`id`, `username`, `email`, `passhash`, `secret`, `title`, '2', '1', `uploaded`, `downloaded`, 
        IF(`last_login`=0,'0000-00-00 00:00:00', from_unixtime(`last_login`)), IF(`last_access`=0,'0000-00-00 00:00:00', from_unixtime(`last_access`)), 
        `Ip`, `passkey`, `bonuspoints`, `freeslots`, IF( `ec`.`name` is null, 'Empornium', REPLACE( `ec`.`name`, ' ', '-' )) FROM emp.users AS eu LEFT JOIN emp.countries AS ec ON ec.id = eu.country;

UPDATE `gazelle`.`users_main` 
SET  `Flag` = 'Antigua-and-Barbuda'
WHERE `Flag` = 'Antigua-Barbuda';

UPDATE `gazelle`.`users_main` 
SET  `Flag` = 'Bosnia-and-Herzegovina'
WHERE `Flag` = 'Bosnia-Herzegovina';

UPDATE `gazelle`.`users_main` 
SET  `Flag` = 'Trinidad-and-Tobago'
WHERE `Flag` = 'Trinidad-&-Tobago';

UPDATE `gazelle`.`users_main` 
SET  `Flag` = 'United-States'
WHERE `Flag` = 'United-States-of-America';

UPDATE `gazelle`.`users_main` 
SET  `Flag` = 'Samoa'
WHERE `Flag` = 'Western-Samoa';

 

-- fetch data from emp.users into gazelle.users_info

insert into `gazelle`.`users_info` (`UserID`, `StyleID`, `Avatar`, `JoinDate`, `Inviter`, `AdminComment`, `Info`, `Warned`, `Donor` )
select `Id`, '3', `avatar`, IF(`added`=0,'0000-00-00 00:00:00', from_unixtime(`added`)), '0', `modcomment`, `info`, IF(`warned`=0,'0000-00-00 00:00:00', from_unixtime(`warned`)), `donor` from `emp`.`users`;

-- set gazelle.users_main.enabled to 1 where emp.users.enabled='yes'

UPDATE `gazelle`.`users_main`
SET `enabled` = '1'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `enabled`='yes');

-- lets auto confirm users stuck in pending status or we'll lose them

UPDATE `gazelle`.`users_main`
SET `enabled` = '1'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `status`='pending' and `enabled`='no');

-- disable users that are confirmed and enable=0, banned users.
UPDATE `gazelle`.`users_main`
SET `enabled` = '2'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `status`='confirmed' and `enabled`='no');

-- set the correct class for the user

-- Apprentice
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '2'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='0');

-- Good Perv
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '4'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='1');

-- Sextreme Perv
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '5'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='2');

-- Smut Peddler
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '6'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='3');

--  MODS
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '11'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='4');

-- ADMINS
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '1'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='5');

-- SYSOP
UPDATE `gazelle`.`users_main`
SET `PermissionID` = '15'
WHERE EXISTS (SELECT 1 from `emp`.`users` WHERE `emp`.`users`.`id`=`gazelle`.`users_main`.`id` and `class`='6');

INSERT INTO `gazelle`.`invite_tree` (`UserID`, `InviterID`, `TreePosition`, `TreeID`, `TreeLevel`) VALUES ('0', '0', '1', '0', '1');


-- Import friends and blocks
INSERT INTO `gazelle`.`friends` (`UserID`, `FriendID`, `Comment`, `Type`) 
SELECT `userid`,`blockid`,'','blocked' 
FROM emp.blocks;
INSERT IGNORE INTO `gazelle`.`friends` (`UserID`, `FriendID`, `Comment`, `Type`) 
SELECT `userid`,`friendid`,'','friends' 
FROM emp.friends;




-- Import the forum
insert into `gazelle`.`forums_posts` (`ID`, `TopicID`, `AuthorID`, `AddedTime`, `Body`, `EditedUserID`, `EditedTime`)
select `id`, `topicid`, `userid`, from_unixtime(`added`), `body`, `editedby`, from_unixtime(`editedat`) from `emp`.`posts`;

--

INSERT INTO gazelle.forums_topics (ID, Title, AuthorID, IsLocked, IsSticky, ForumID, NumPosts, LastPostID, LastPostTime, LastPostAuthorID)
SELECT id, subject, userid, 0, if(sticky='yes', '1', '0') as sticky, forumid,
(select count(*) as count from emp.posts where emp.posts.topicid=emp.topics.id) as numposts,
lastpost, 
(select from_unixtime(added) as added from emp.posts where emp.posts.id=lastpost) as time,
(select userid from emp.posts where emp.posts.id=emp.topics.lastpost) as authorid
FROM
emp.topics;

--

insert into gazelle.forums (ID, CategoryID, Sort, Name, Description, NumTopics, NumPosts, LastPostID, LastPostAuthorID, LastPostTopicID, LastPostTime)
select id, 1, sort, Name, description, topiccount, postcount, 

(select p.id from emp.topics as t
inner join emp.posts as p on t.id=p.topicid
where t.forumid = emp.forums.id
order by p.added desc limit 1) as LastPostId,

(select p.userid from emp.topics as t
inner join emp.posts as p on t.id=p.topicid
where t.forumid = emp.forums.id
order by p.added desc limit 1) as LastPostAuthorID,

(select p.topicid from emp.topics as t
inner join emp.posts as p on t.id=p.topicid
where t.forumid = emp.forums.id
order by p.added desc limit 1) as LastPostTopicID,

(select from_unixtime(p.added) from emp.topics as t
inner join emp.posts as p on t.id=p.topicid
where t.forumid = emp.forums.id
order by p.added desc limit 1) as LastPostTime

from emp.forums;

--

insert into gazelle.forums_last_read_topics (UserID, TopicID, PostID)
select userid, topicid, lastpostread
from emp.readposts
group by userid, topicid;

-- Import PM's
insert into gazelle.pm_conversations (ID, Subject)
select id, if(subject<>'', subject, 'no subject') as subject from emp.messages;

insert into gazelle.pm_messages (ConvID, SentDate, SenderID, Body)
select id, from_unixtime(added), sender, msg from emp.messages;

insert into gazelle.pm_conversations_users (UserID, ConvID, InInbox, InSentbox, SentDate, ReceivedDate, UnRead)
select sender, id, 0, 1, from_unixtime(added), from_unixtime(added), 0 from emp.messages where sender > 0 and sender <> receiver;

insert into gazelle.pm_conversations_users (UserID, ConvID, InInbox, InSentbox, SentDate, ReceivedDate, UnRead)
select receiver, id, 1, 0, from_unixtime(added), from_unixtime(added), 0 from emp.messages where sender > 0 and sender <> receiver;