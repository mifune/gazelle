
<?

error('nonono');

/*
$DB->query("SELECT SQL_CALC_FOUND_ROWS
                   UserID, TorrentID, Count(TorrentID), Max( Time ) 
              FROM users_downloads 
          GROUP BY UserID, TorrentID
            HAVING Count(TorrentID)>1
          ORDER BY Count(TorrentID) DESC
             LIMIT $Limit"); */

$DB->query("DROP TABLE IF EXISTS fixit"); // jsut in case!
$DB->query("CREATE TABLE `fixit` (  
  `UserID` int(11) NOT NULL,
  `TorrentID` int(11) NOT NULL,
  `Count` int(11) NOT NULL,
  `Time` datetime NOT NULL,
  PRIMARY KEY ( UserID, TorrentID )
) ENGINE=InnoDB DEFAULT CHARSET=utf8"); 

$DB->query("INSERT INTO fixit 
            SELECT UserID, TorrentID, Count(TorrentID), Max( Time ) from users_downloads 
            group by UserID, TorrentID
            having Count(TorrentID) > 1;"); 



$DB->query("DELETE u FROM users_downloads AS u JOIN fixit AS f ON u.UserID=f.UserID AND u.TorrentID = f.TorrentID
            WHERE f.Time != u.Time;");


//"LOCK TABLE fixit write, users_downloads write";








"create table fixit (user_2, user_1, type, timestamp, n, primary key( user_2, user_1, type) );
lock table fixit write, user_interactions u write, user_interactions write;

insert into fixit 
select user_2, user_1, type, max(timestamp), count(*) n from user_interactions u 
group by user_2, user_1, type
having n > 1;

delete u from user_interactions u, fixit 
where fixit.user_2 = u.user_2 
  and fixit.user_1 = u.user_1 
  and fixit.type = u.type 
  and fixit.timestamp != u.timestamp;

alter table user_interactions add primary key (user_2, user_1, type );

unlock tables;";







?>