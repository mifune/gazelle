<?
set_time_limit(50000);
ob_end_flush();
gc_enable();
//TODO: make it awesome, make it flexible!
//INSERT INTO users_geodistribution (Code, Users) SELECT g.Code, COUNT(u.ID) AS Users FROM geoip_country AS g JOIN users_main AS u ON INET_ATON(u.IP) BETWEEN g.StartIP AND g.EndIP WHERE u.Enabled='1' GROUP BY g.Code ORDER BY Users DESC

/*************************************************************************\
//--------------Schedule page -------------------------------------------//

This page is run every 15 minutes, by cron.

\*************************************************************************/

function next_biweek() {
	$Date = date('d');
	if($Date < 22 && $Date >=8) {
		$Return = 22;
	} else {
		$Return = 8;
	}
	return $Return;
}

function next_day() {
	$Tomorrow = time(0,0,0,date('m'),date('d')+1,date('Y'));
	return date('d', $Tomorrow);
}

function next_hour() {
	$Hour = time(date('H')+1,0,0,date('m'),date('d'),date('Y'));
	return date('H', $Hour);
}

if ((!isset($argv[1]) || $argv[1]!=SCHEDULE_KEY) && !check_perms('admin_schedule')) { // authorization, Fix to allow people with perms hit this page.
	error(403);
}

if (check_perms('admin_schedule')) {
	authorize();
	show_header();
	echo '<pre>';
}

$DB->query("SELECT NextHour, NextDay, NextBiWeekly FROM schedule");
list($Hour, $Day, $BiWeek) = $DB->next_record();
$DB->query("UPDATE schedule SET NextHour = ".next_hour().", NextDay = ".next_day().", NextBiWeekly = ".next_biweek());

$sqltime = sqltime();

echo "$sqltime\n";


$DB->query("SELECT AutoDelete, DeleteRecordsMins, KeepSpeed FROM site_options");
list($AutoDelete,$DeleteRecordsMins,$KeepSpeed) = $DB->next_record();

/*************************************************************************\
//--------------Run every time ------------------------------------------//

These functions are run every time the script is executed (every 15
minutes).

\*************************************************************************/

echo "Ran every-time functions\n";

 
sleep(5);

//------------- Delete unpopular tags -------// moved to vote_tag so torrents_group.taglist can be adjusted if tag is deleted
//$DB->query("DELETE FROM torrents_tags WHERE NegativeVotes>PositiveVotes");

//------------- Expire old FL Tokens and clear cache where needed ------//
$sqltime = sqltime();
$DB->query("SELECT DISTINCT UserID from users_slots WHERE FreeLeech < '$sqltime' AND DoubleSeed < '$sqltime'");
while (list($UserID) = $DB->next_record()) {
	$Cache->delete_value('users_tokens_'.$UserID[0]);
}

$DB->query("SELECT us.UserID, t.info_hash 
            FROM users_slots AS us 
            JOIN torrents AS t ON us.TorrentID = t.ID
			WHERE FreeLeech < '$sqltime' AND DoubleSeed < '$sqltime'");
while (list($UserID,$InfoHash) = $DB->next_record(MYSQLI_NUM, false)) {
	update_tracker('remove_tokens', array('info_hash' => rawurlencode($InfoHash), 'userid' => $UserID));
}

//-------Gives credits to users with active torrents-------------------------//
sleep(3);
/*
// method 1 : capped at 60 - linear rate,  ~2.8s with 650k seeders
$DB->query("update users_main
            set Credits = Credits +
                (select if(count(*) < 60, count(*), 60) * 0.25 from xbt_files_users
                where users_main.ID = xbt_files_users.uid AND xbt_files_users.remaining = 0 AND xbt_files_users.active = 1)");

 // method 2 : no cap, diminishing returns, ~3.2s with 650k seeders 
$DB->query("UPDATE users_main SET Credits = Credits + 
           ( SELECT ROUND( ( SQRT( 8.0 * ( COUNT(*)/20 ) + 1.0 ) - 1.0 ) / 2.0 *20 ) * 0.25
                    FROM xbt_files_users
                    WHERE users_main.ID = xbt_files_users.uid
                    AND xbt_files_users.remaining =0
                    AND xbt_files_users.active =1 )"); 
*/
        
// method 3 : no cap, diminishing returns , rewritten as join and also records seedhours, ~2.1s with 650k seeders
$DB->query("UPDATE users_main AS um
              JOIN (
                      SELECT xbt_files_users.uid AS UserID,
                           (ROUND( ( SQRT( 8.0 * ( COUNT(*)/20 ) + 1.0 ) - 1.0 ) / 2.0 *20 ) * 0.25 ) AS SeedCount,
                           (COUNT(*) * 0.25 ) AS SeedHours
                        FROM xbt_files_users
                       WHERE xbt_files_users.remaining =0
                         AND xbt_files_users.active =1
                    GROUP BY xbt_files_users.uid
                   ) AS s ON s.UserID=um.ID
               SET Credits=Credits+SeedCount,
              CreditsDaily=CreditsDaily+SeedCount,
              um.SeedHours=um.SeedHours+s.SeedHours,
         um.SeedHoursDaily=um.SeedHoursDaily+s.SeedHours ");
 
                        

//------------ record ip's/ports for users and refresh time field for existing status records -------------------------//
sleep(3);
$nowtime = time();

$DB->query("INSERT INTO users_connectable_status ( UserID, IP, Time )
        SELECT uid, ip, '$nowtime' FROM xbt_files_users GROUP BY uid, ip
         ON DUPLICATE KEY UPDATE Time='$nowtime'");


//------------Remove inactive peers every 15 minutes-------------------------//
$DB->query("DELETE FROM xbt_files_users WHERE active='0'");












//------------- Remove torrents that have expired their warning period every 15 minutes ----------//

echo "AutoDelete torrents marked for deletion: ". ($AutoDelete?'On':'Off')."\n";
if ($AutoDelete){
    include(SERVER_ROOT.'/sections/tools/managers/mfd_functions.php');

    $Torrents = get_torrents_under_review('warned', true); 
    $NumTorrents = count($Torrents);
    //echo "Num to auto-delete: $NumTorrents\n";
    if($NumTorrents>0){
        $NumDeleted = delete_torrents_list($Torrents);
        echo "Num of torrents auto-deleted: $NumDeleted\n";
    }
}




/*************************************************************************\
//--------------Run every hour ------------------------------------------//

These functions are run every hour.

\*************************************************************************/


if($Hour != next_hour() || $_GET['runhour'] || isset($argv[2])){
	echo "Ran hourly functions\n";
	
    
        
    // ---------- remove old requests (and return bounties) -------------
    
    // return bounties for each voter
    $DB->query("SELECT r.ID, r.Title, v.UserID, v.Bounty
                  FROM requests as r JOIN requests_votes as v ON v.RequestID=r.ID
                 WHERE TorrentID='0' AND TimeAdded < '".time_minus(3600*24*91)."'" );
    
	$RemoveBounties = $DB->to_array();
    $RemoveRequestIDs = array();
    
    foreach($RemoveBounties as $BountyInfo) {
        list($RequestID, $Title, $UserID, $Bounty) = $BountyInfo;
        // collect unique request ID's the old fashioned way
        if (!in_array($RequestID, $RemoveRequestIDs)) $RemoveRequestIDs[] = $RequestID;
        // return bounty and log in staff notes
        $Title = db_string($Title);
		$DB->query("UPDATE users_info AS ui JOIN users_main AS um ON um.ID = ui.UserID
                       SET um.Uploaded=(um.Uploaded+'$Bounty'),
                           ui.AdminComment = CONCAT('".$sqltime." - Bounty of " . get_size($Bounty). " returned from expired Request $RequestID ($Title).\n', ui.AdminComment)
			         WHERE ui.UserID = '$UserID'");
        // send users who got bounty returned a PM
        send_pm($UserID, 0, "Bounty returned from expired request", "Your bounty of " . get_size($Bounty). " has been returned from the expired Request $RequestID ($Title).");
     
    }
    
    if (count($RemoveRequestIDs)>0) {
        // log and update sphinx for each request
        $DB->query("SELECT r.ID, r.Title, Count(v.UserID), SUM( v.Bounty), r.GroupID 
                      FROM requests as r JOIN requests_votes as v ON v.RequestID=r.ID 
                     WHERE r.ID IN(".implode(",", $RemoveRequestIDs).")
                     GROUP BY r.ID" );

        $RemoveRequests = $DB->to_array();

        // delete the requests
        $DB->query("DELETE r, v, t, c
                      FROM requests as r 
                 LEFT JOIN requests_votes as v ON r.ID=v.RequestID 
                 LEFT JOIN requests_tags AS t ON r.ID=t.RequestID 
                 LEFT JOIN requests_comments AS c ON r.ID=c.RequestID
                     WHERE r.ID IN(".implode(",", $RemoveRequestIDs).")"); 

        //log and update sphinx (sphinx call must be done after requests are deleted)    
        foreach($RemoveRequests as $Request) {
            list($RequestID, $Title, $NumUsers, $Bounty, $GroupID) = $Request;

            write_log("Request $RequestID ($Title) expired - returned total of ". get_size($Bounty)." to $NumUsers users");

            $Cache->delete_value('request_votes_'.$RequestID);
            if ($GroupID) {
                $Cache->delete_value('requests_group_'.$GroupID);
            }
            update_sphinx_requests($RequestID);

        }
    }
    
    
    
    //----------------------------------------
	
    
      
	//if ($Hour%3 == 0) { // every 3 hrs
      //}
	//------------- Award Badges ----------------------------------------//
       
      
      include(SERVER_ROOT.'/sections/schedule/award_badges.php');
      
      
      
	//------------- Record daily seedhours  ----------------------------------------//

	if ($Hour == 3) { // 3 am servertime... want it to be daily but not on the 0 hour  //SeedHours>0.00 
 
        /*
            $DB->query("UPDATE users_main AS u JOIN users_info AS i ON u.ID=i.UserID
                           SET BonusLog = CONCAT('$sqltime | +', CreditsDaily, ' credits | seeded ', SeedHoursDaily, ' hrs\n', BonusLog),
                               SeedHistory = CONCAT('$sqltime | ', SeedHoursDaily, ' hrs | up: ', 
                                                FORMAT((Uploaded-UploadedLast)/1073741824, 2) , ' GB | down: ', 
                                                FORMAT((Downloaded-DownloadedLast)/1073741824, 2) , ' GB | ', CreditsDaily, ' credits\n', SeedHistory),
                               SeedHoursDaily=0.00, 
                               CreditsDaily=0.00 , 
                               UploadedLast=Uploaded , 
                               DownloadedLast=Downloaded 
                         WHERE SeedHoursDaily>0.00");   */
            
            $DB->query("UPDATE users_main AS u JOIN users_info AS i ON u.ID=i.UserID
                           SET BonusLog = CONCAT('$sqltime | +', CreditsDaily, ' credits | seeded ', SeedHoursDaily, ' hrs\n', BonusLog),
                               SeedHistory = CONCAT('$sqltime | ', SeedHoursDaily, ' hrs | up: ', 
                                                FORMAT(UploadedDaily/1073741824, 2) , ' GB | down: ', 
                                                FORMAT(DownloadedDaily/1073741824, 2) , ' GB | ', CreditsDaily, ' credits\n', SeedHistory),
                               SeedHoursDaily=0.00, 
                               CreditsDaily=0.00 , 
                               UploadedDaily=0.00 , 
                               DownloadedDaily=0.00 
                         WHERE SeedHoursDaily>0.00");
            
            
      }
      
      
	//------------- Front page stats ----------------------------------------//
 
	//Love or hate, this makes things a hell of a lot faster

	if ($Hour%2 == 0) {
		$DB->query("SELECT COUNT(uid) AS Snatches FROM xbt_snatched");
		list($SnatchStats) = $DB->next_record();
		$Cache->cache_value('stats_snatches',$SnatchStats,0);
	}

       
	$DB->query("SELECT IF(remaining=0,'Seeding','Leeching') AS Type, COUNT(uid) FROM xbt_files_users WHERE active=1 GROUP BY Type");
	$PeerCount = $DB->to_array(0, MYSQLI_NUM, false);
	$SeederCount = isset($PeerCount['Seeding'][1]) ? $PeerCount['Seeding'][1] : 0;
	$LeecherCount = isset($PeerCount['Leeching'][1]) ? $PeerCount['Leeching'][1] : 0;
	$Cache->cache_value('stats_peers',array($LeecherCount,$SeederCount),0);
      
	if ($Hour%6 == 0) { // 4 times a day record site history
           
            $DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1'");
            list($UserCount) = $DB->next_record();
            $Cache->cache_value('stats_user_count', $UserCount, 0);
            
            $DB->query("SELECT COUNT(ID) FROM torrents");
            list($TorrentCount) = $DB->next_record();
            $Cache->cache_value('stats_torrent_count', $TorrentCount, 0);
            
            $DB->query("INSERT INTO site_stats_history ( TimeAdded, Users, Torrents, Seeders, Leechers )
                                 VALUES ('".sqltime()."','$UserCount','$TorrentCount','$SeederCount','$LeecherCount')");
            $DB->delete_value('site_stats');
      }

	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24)."'");
	list($UserStats['Day']) = $DB->next_record();

	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24*7)."'");
	list($UserStats['Week']) = $DB->next_record();

	$DB->query("SELECT COUNT(ID) FROM users_main WHERE Enabled='1' AND LastAccess>'".time_minus(3600*24*30)."'");
	list($UserStats['Month']) = $DB->next_record();

	$Cache->cache_value('stats_users',$UserStats,0);
	
	//------------- Record who's seeding how much, used for ratio watch
	
	$DB->query("TRUNCATE TABLE users_torrent_history_temp");
	$DB->query("INSERT INTO users_torrent_history_temp 
		(UserID, NumTorrents) 
		SELECT uid, 
		COUNT(DISTINCT fid) 
		FROM xbt_files_users 
		WHERE mtime>unix_timestamp(now()-interval 1 hour) 
		AND Remaining=0
		GROUP BY uid;");
	$DB->query("UPDATE users_torrent_history AS h 
		JOIN users_torrent_history_temp AS t ON t.UserID=h.UserID AND t.NumTorrents=h.NumTorrents 
		SET h.Finished='0', 
		h.LastTime=unix_timestamp(now()) 
		WHERE h.Finished='1' 
		AND h.Date=UTC_DATE()+0;");
	$DB->query("INSERT INTO users_torrent_history
		(UserID, NumTorrents, Date)
		SELECT UserID, NumTorrents, UTC_DATE()+0
		FROM users_torrent_history_temp
		ON DUPLICATE KEY UPDATE
		Time=Time+(unix_timestamp(NOW())-LastTime),
		LastTime=unix_timestamp(NOW());");

	//------------- Promote users -------------------------------------------//
	sleep(5);
	$Criteria = array();
	$Criteria[]=array('From'=>APPRENTICE, 'To'=>PERV, 'MinUpload'=>10*1024*1024*1024, 'MinRatio'=>0.7, 'MinUploads'=>0, 'TotalUploaded'=>0, 'MaxTime'=>time_minus(3600*24*7));
	$Criteria[]=array('From'=>PERV, 'To'=>GOOD_PERV, 'MinUpload'=>25*1024*1024*1024, 'MinRatio'=>1.05, 'MinUploads'=>0, 'TotalUploaded'=>0, 'MaxTime'=>time_minus(3600*24*7*4));
	$Criteria[]=array('From'=>GOOD_PERV, 'To'=>SEXTREME_PERV, 'MinUpload'=>1024*1024*1024*1024, 'MinRatio'=>1.05, 'MinUploads'=>50, 'TotalUploaded'=>0, 'MaxTime'=>time_minus(3600*24*7*13));
	$Criteria[]=array('From'=>SEXTREME_PERV, 'To'=>SMUT_PEDDLER, 'MinUpload'=>10*1024*1024*1024*1024, 'MinRatio'=>1.05, 'MinUploads'=>250, 'TotalUploaded'=>0, 'MaxTime'=>time_minus(3600*24*7*26));

      foreach($Criteria as $L){ // $L = Level
            if ($L[TotalUploaded]>0){
            /*      This Query uses MinUploads OR TotalUploaded (from users torrents) as torrents up criteria  -- used if TotalUploaded is set      */
                $Query = "SELECT ID FROM users_main JOIN users_info ON users_main.ID = users_info.UserID
                        WHERE PermissionID=".$L['From']."
                        AND Warned= '0000-00-00 00:00:00'
                        AND JoinDate<'$L[MaxTime]'
                        AND (Uploaded/Downloaded >='$L[MinRatio]' OR (Uploaded/Downloaded IS NULL))
                        AND Uploaded>='$L[MinUpload]'
                        AND ((SELECT COUNT(ID) FROM torrents WHERE UserID=users_main.ID)>='$L[MinUploads]'
                            OR (SELECT SUM(Size) FROM torrents WHERE UserID=users_main.ID)>='$L[TotalUploaded]')
                        AND Enabled='1'";
            } else {   // if  TotalUploaded (from users torrents)==0 then just use MinUploads as torrents up criteria
                $Query = "SELECT ID FROM users_main JOIN users_info ON users_main.ID = users_info.UserID
                        WHERE PermissionID=".$L['From']."
                        AND Warned= '0000-00-00 00:00:00'
                        AND Uploaded>='$L[MinUpload]'
                        AND (Uploaded/Downloaded >='$L[MinRatio]' OR (Uploaded/Downloaded IS NULL))
                        AND JoinDate<'$L[MaxTime]'
                        AND (SELECT COUNT(ID) FROM torrents WHERE UserID=users_main.ID)>='$L[MinUploads]'
                        AND Enabled='1'";
            }
		if (!empty($L['Extra'])) {
			$Query .= ' AND '.$L['Extra'];
		}
		
		$DB->query($Query);

		$UserIDs = $DB->collect('ID');
            $NumPromotions = count($UserIDs);
            
		if ($NumPromotions > 0) {
			foreach($UserIDs as $UserID) {
				/*$Cache->begin_transaction('user_info_'.$UserID);
				$Cache->update_row(false, array('PermissionID'=>$L['To']));
				$Cache->commit_transaction(0);*/
				$Cache->delete_value('user_info_'.$UserID);
				$Cache->delete_value('user_info_heavy_'.$UserID);
				$Cache->delete_value('user_stats_'.$UserID);
				$Cache->delete_value('enabled_'.$UserID);
				$DB->query("UPDATE users_info SET AdminComment = CONCAT('".sqltime()." - Class changed to ".make_class_string($L['To'])." by System\n', AdminComment) WHERE UserID = ".$UserID);
			}		
			$DB->query("UPDATE users_main SET PermissionID=".$L['To']." WHERE ID IN(".implode(',',$UserIDs).")");
		
                  echo "Promoted $NumPromotions user".($NumPromotions>1?'s':'')." to ".make_class_string($L['To'])."\n";
            }

/*
 * Lanz: Lets not demote anyone that doesn't have the required upload today but perhaps "unfairly" got promoted earlier.
 * Mifune: Required uploads is now only for promotions, not demotions (to avoid users being demoted by the torrent reaper)
		// Demote users with less than the required uploads
		
		$Query = "SELECT ID FROM users_main JOIN users_info ON users_main.ID = users_info.UserID
			WHERE PermissionID='$L[To]'
			AND ( Uploaded<'$L[MinUpload]'
			OR (SELECT COUNT(ID) FROM torrents WHERE UserID=users_main.ID)<'$L[MinUploads]'";
			if (!empty($L['Extra'])) {
				$Query .= " OR NOT ".$L['Extra'];
			}
			$Query .= ")
			AND Enabled='1'
			AND ID NOT IN (213461)";
		
		$DB->query($Query);
		$UserIDs = $DB->collect('ID');
		
		if (count($UserIDs) > 0) {
			foreach($UserIDs as $UserID) {
				$Cache->delete_value('user_info_'.$UserID);
				$Cache->delete_value('user_info_heavy_'.$UserID);
				$Cache->delete_value('user_stats_'.$UserID);
				$Cache->delete_value('enabled_'.$UserID);
				$DB->query("UPDATE users_info SET AdminComment = CONCAT('".sqltime()." - Class changed to ".make_class_string($L['From'])." by System\n\n', AdminComment) WHERE UserID = ".$UserID);
			}
			$DB->query("UPDATE users_main SET PermissionID=".$L['From']." WHERE ID IN(".implode(',',$UserIDs).")");
		}
*/                
	}


	//------------- Expire invites ------------------------------------------//
	sleep(3);
	$DB->query("SELECT InviterID FROM invites WHERE Expires<'$sqltime'");
	$Users = $DB->to_array();
	foreach ($Users as $UserID) {
		list($UserID) = $UserID;
		$DB->query("SELECT Invites FROM users_main WHERE ID=$UserID");
		list($Invites) = $DB->next_record();
		if ($Invites < 10) {
			$DB->query("UPDATE users_main SET Invites=Invites+1 WHERE ID=$UserID");
			$Cache->begin_transaction('user_info_heavy_'.$UserID);
			$Cache->update_row(false, array('Invites' => '+1'));
			$Cache->commit_transaction(0);
		}
	}
	$DB->query("DELETE FROM invites WHERE Expires<'$sqltime'");


	//------------- Hide old requests ---------------------------------------//
	sleep(3);
	$DB->query("UPDATE requests SET Visible = 0 WHERE TimeFilled < (NOW() - INTERVAL 7 DAY) AND TimeFilled <> '0000-00-00 00:00:00'");

	//------------- Remove dead peers ---------------------------------------//
	sleep(3);
        $DB->query("DELETE FROM xbt_files_users WHERE mtime<unix_timestamp(now()-interval 2 HOUR)");

	//------------- Remove dead sessions ---------------------------------------//
	sleep(3);
	
	$AgoDays = time_minus(3600*24*30);	
	$DB->query("SELECT UserID, SessionID FROM users_sessions WHERE Active = 1 AND LastUpdate<'$AgoDays' AND KeepLogged='1'");
	while(list($UserID,$SessionID) = $DB->next_record()) {
		$Cache->begin_transaction('users_sessions_'.$UserID);
		$Cache->delete_row($SessionID);
		$Cache->commit_transaction(0);
	}
	
	
	$DB->query("DELETE FROM users_sessions WHERE LastUpdate<'$AgoDays' AND KeepLogged='1'");
	
	
	$AgoMins = time_minus(60*30);
	$DB->query("SELECT UserID, SessionID FROM users_sessions WHERE Active = 1 AND LastUpdate<'$AgoMins' AND KeepLogged='0'");
	while(list($UserID,$SessionID) = $DB->next_record()) {
		$Cache->begin_transaction('users_sessions_'.$UserID);
		$Cache->delete_row($SessionID);
		$Cache->commit_transaction(0);
	}
	
	
	$DB->query("DELETE FROM users_sessions WHERE LastUpdate<'$AgoMins' AND KeepLogged='0'");
	

	
	//------------- Lower Login Attempts ------------------------------------//
	$DB->query("UPDATE login_attempts SET Attempts=Attempts-1 WHERE Attempts>0");
	$DB->query("DELETE FROM login_attempts WHERE LastAttempt<'".time_minus(3600*24*90)."'");

        //------------- Remove expired warnings ---------------------------------//
        $DB->query("SELECT UserID FROM users_info WHERE Warned<'$sqltime'");
        while(list($UserID) = $DB->next_record()) {
                $Cache->begin_transaction('user_info_'.$UserID);
                $Cache->update_row(false, array('Warned'=>'0000-00-00 00:00:00'));
                $Cache->commit_transaction(2592000);
        }

        $DB->query("UPDATE users_info SET Warned='0000-00-00 00:00:00' WHERE Warned<'$sqltime'");

	// If a user has downloaded more than 10 gigs while on ratio watch, banhammer
/*
        $DB->query("SELECT ID FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
                WHERE i.RatioWatchEnds!='0000-00-00 00:00:00'
                AND i.RatioWatchDownload+10*1024*1024*1024<m.Downloaded
                And m.Enabled='1'");

        $UserIDs = $DB->collect('ID');
        if(count($UserIDs) > 0) {
                disable_users($UserIDs, "Disabled by ratio watch system for downloading more than 10 gigs on ratio watch.", 2);
        }
 */
        
	$UserQuery = $DB->query("SELECT ID, torrent_pass FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
                WHERE i.RatioWatchEnds!='0000-00-00 00:00:00'
                AND i.RatioWatchDownload+10*1024*1024*1024<m.Downloaded
                And m.Enabled='1'
                AND m.can_leech='1'");
         

	$UserIDs = $DB->collect('ID');
	if(count($UserIDs) > 0) {
		$DB->query("UPDATE users_info AS i JOIN users_main AS m ON m.ID=i.UserID
			SET 
			m.can_leech='0',
			i.AdminComment=CONCAT('$sqltime - Leeching ability disabled by ratio watch system for downloading more than 10 gigs on ratio watch - required ratio: ', m.RequiredRatio,'
'			, i.AdminComment)
			WHERE m.ID IN(".implode(',',$UserIDs).")");
	}
	
	foreach($UserIDs as $UserID) {
		$Cache->begin_transaction('user_info_heavy_'.$UserID);
		$Cache->update_row(false, array('RatioWatchDownload'=>0, 'CanLeech'=>0));
		$Cache->commit_transaction(0);
		send_pm($UserID, 0, db_string("Your downloading rights have been disabled"), db_string("As you downloaded more than 10GB whilst on ratio watch your downloading rights have been revoked. You will not be able to download any torrents until your ratio is above your new required ratio."), '');
		echo "Ratio watch leeching disabled (>10GB): $UserID\n";
	}

	$DB->set_query_id($UserQuery);
	$Passkeys = $DB->collect('torrent_pass');
	foreach($Passkeys as $Passkey) {
		update_tracker('update_user', array('passkey' => $Passkey, 'can_leech' => '0'));
	}
        
	sleep(6);
        
}
/*************************************************************************\
//--------------Run every day -------------------------------------------//

These functions are run in the first 15 minutes of every day.

\*************************************************************************/

if($Day != next_day() || $_GET['runday']){
	echo "Ran daily functions\n";
	if($Day%2 == 0) { // If we should generate the drive database (at the end)
		$GenerateDriveDB = true;
	}
    

    
    $DB->query("SELECT COUNT(ID) FROM torrents WHERE Time > '".time_minus(3600*24)."'");
    list($TorrentCountLastDay) = $DB->next_record();
    $Cache->cache_value('stats_torrent_count_daily', $TorrentCountLastDay, 0); //inf cache
    
      /*  a join on a function(!?!?) with 100's of 1000's of records is a bad idea....
       * // this is now done differently,... ipcc is set for each user on login - see below
      $DB->query("INSERT INTO users_geodistribution (Code, Users) 
                       SELECT g.Code, COUNT(u.ID) AS Users 
                         FROM geoip_country AS g JOIN users_main AS u ON INET_ATON(u.IP) BETWEEN g.StartIP AND g.EndIP 
                        WHERE u.Enabled='1' 
                        GROUP BY g.Code 
                     ORDER BY Users DESC");
    */
 
    $DB->query("TRUNCATE TABLE users_geodistribution");
    $DB->query("INSERT INTO users_geodistribution (Code, Users) 
                       SELECT ipcc, COUNT(ID) AS NumUsers 
                         FROM users_main 
                        WHERE Enabled='1' AND ipcc != ''
                        GROUP BY ipcc 
                     ORDER BY NumUsers DESC");
	$Cache->delete_value('geodistribution');
    
    // -------------- clean up users_connectable_status table - remove values older than 60 days
    
	$DB->query("DELETE FROM users_connectable_status WHERE Time<(".(int)(time() - (3600*24*60)).")");
   
    
	//------------- Ratio requirements
	
	$DB->query("DELETE FROM users_torrent_history WHERE Date<date('".sqltime()."'-interval 7 day)+0");
	$DB->query("TRUNCATE TABLE users_torrent_history_temp;");
	$DB->query("INSERT INTO users_torrent_history_temp 
		(UserID, SumTime)
		SELECT UserID, SUM(Time) FROM users_torrent_history
		GROUP BY UserID;");
	$DB->query("INSERT INTO users_torrent_history
		(UserID, NumTorrents, Date, Time)
		SELECT UserID, 0, UTC_DATE()+0, 259200-SumTime
		FROM users_torrent_history_temp
		WHERE SumTime<259200;");
	$DB->query("UPDATE users_torrent_history SET Weight=NumTorrents*Time;");
	$DB->query("TRUNCATE TABLE users_torrent_history_temp;");
	$DB->query("INSERT INTO users_torrent_history_temp 
		(UserID, SeedingAvg)
		SELECT UserID, SUM(Weight)/SUM(Time) FROM users_torrent_history
		GROUP BY UserID;");
	$DB->query("DELETE FROM users_torrent_history WHERE NumTorrents='0'");
	$DB->query("TRUNCATE TABLE users_torrent_history_snatch;");
	$DB->query("INSERT INTO users_torrent_history_snatch(UserID, NumSnatches) 
		SELECT 
		xs.uid,
		COUNT(DISTINCT xs.fid)
		FROM
		xbt_snatched AS xs
		join torrents on torrents.ID=xs.fid
		GROUP BY xs.uid;");
	$DB->query("UPDATE users_main AS um
		JOIN users_torrent_history_temp AS t ON t.UserID=um.ID
		JOIN users_torrent_history_snatch AS s ON s.UserID=um.ID
		SET um.RequiredRatioWork=(1-(t.SeedingAvg/s.NumSnatches))
		WHERE s.NumSnatches>0;");
	
    /*  // OLD Ratio Reqs - we have lowered them to 0.50 max 
	$RatioRequirements = array(
		array(80*1024*1024*1024, 0.60, 0.50),
		array(60*1024*1024*1024, 0.60, 0.40),
		array(50*1024*1024*1024, 0.60, 0.30),
		array(40*1024*1024*1024, 0.50, 0.20),
		array(30*1024*1024*1024, 0.40, 0.10),
		array(20*1024*1024*1024, 0.30, 0.05),
		array(10*1024*1024*1024, 0.20, 0.0),
		array(5*1024*1024*1024,  0.15, 0.0)
	);  
     */
	
	
	$RatioRequirements = array(
		array(80*1024*1024*1024, 0.50, 0.40),
		array(60*1024*1024*1024, 0.50, 0.30),
		array(50*1024*1024*1024, 0.50, 0.20),
		array(40*1024*1024*1024, 0.40, 0.10),
		array(30*1024*1024*1024, 0.30, 0.05),
		array(20*1024*1024*1024, 0.20, 0.0),
		array(10*1024*1024*1024, 0.15, 0.0),
		array(5*1024*1024*1024,  0.10, 0.0)
	);
    
    
	$DB->query("UPDATE users_main SET RequiredRatio=0.50 WHERE Downloaded>100*1024*1024*1024");
	
	
	
	$DownloadBarrier = 100*1024*1024*1024;
	foreach($RatioRequirements as $Requirement) {
		list($Download, $Ratio, $MinRatio) = $Requirement;
		
		$DB->query("UPDATE users_main SET RequiredRatio=RequiredRatioWork*$Ratio WHERE Downloaded >= '$Download' AND Downloaded < '$DownloadBarrier'");
		
		$DB->query("UPDATE users_main SET RequiredRatio=$MinRatio WHERE Downloaded >= '$Download' AND Downloaded < '$DownloadBarrier' AND RequiredRatio<$MinRatio");
		
		$DB->query("UPDATE users_main SET RequiredRatio=$Ratio WHERE Downloaded >= '$Download' AND Downloaded < '$DownloadBarrier' AND can_leech='0' AND Enabled='1'");
		
		$DownloadBarrier = $Download;
	}
	
	$DB->query("UPDATE users_main SET RequiredRatio=0.00 WHERE Downloaded<5*1024*1024*1024");
	

	// Here is where we manage ratio watch
	
	sleep(4);
	$OffRatioWatch = array();
	$OnRatioWatch = array();
	
	// Take users off ratio watch and enable leeching
	$UserQuery = $DB->query("SELECT m.ID, torrent_pass FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
		WHERE m.Uploaded/m.Downloaded >= m.RequiredRatio
		AND i.RatioWatchEnds!='0000-00-00 00:00:00' 
		AND m.can_leech='0'
		AND m.Enabled='1'");
	$OffRatioWatch = $DB->collect('ID');
	if(count($OffRatioWatch)>0) {
		$DB->query("UPDATE users_info AS ui
			JOIN users_main AS um ON um.ID = ui.UserID
			SET ui.RatioWatchEnds='0000-00-00 00:00:00',
			ui.RatioWatchDownload='0',
			um.can_leech='1',
			ui.AdminComment = CONCAT('".$sqltime." - Leeching re-enabled by adequate ratio.\n', ui.AdminComment)
			WHERE ui.UserID IN(".implode(",", $OffRatioWatch).")");
	}
	
	foreach($OffRatioWatch as $UserID) {
		$Cache->begin_transaction('user_info_heavy_'.$UserID);
		$Cache->update_row(false, array('RatioWatchEnds'=>'0000-00-00 00:00:00','RatioWatchDownload'=>'0','CanLeech'=>1));
		$Cache->commit_transaction(0);
		send_pm($UserID, 0, db_string("You have been taken off Ratio Watch"), db_string("Congratulations! Feel free to begin downloading again.\n To ensure that you do not get put on ratio watch again, please read the rules located [url=/articles.php?topic=ratio]here[/url].\n"), '');
		echo "Ratio watch off: $UserID\n";
	}
	$DB->set_query_id($UserQuery);
	$Passkeys = $DB->collect('torrent_pass');
	foreach($Passkeys as $Passkey) {
		update_tracker('update_user', array('passkey' => $Passkey, 'can_leech' => '1'));
	}

        // Take users off ratio watch 
        $UserQuery = $DB->query("SELECT m.ID, torrent_pass FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
                WHERE m.Uploaded/m.Downloaded >= m.RequiredRatio
                AND i.RatioWatchEnds!='0000-00-00 00:00:00'
                AND m.Enabled='1'");
        $OffRatioWatch = $DB->collect('ID');
        if(count($OffRatioWatch)>0) {
                $DB->query("UPDATE users_info AS ui
                        JOIN users_main AS um ON um.ID = ui.UserID
                        SET ui.RatioWatchEnds='0000-00-00 00:00:00',
                        ui.RatioWatchDownload='0',
                        um.can_leech='1'
                        WHERE ui.UserID IN(".implode(",", $OffRatioWatch).")");
        }

        foreach($OffRatioWatch as $UserID) {
                $Cache->begin_transaction('user_info_heavy_'.$UserID);
                $Cache->update_row(false, array('RatioWatchEnds'=>'0000-00-00 00:00:00','RatioWatchDownload'=>'0','CanLeech'=>1));
                $Cache->commit_transaction(0);
                send_pm($UserID, 0, db_string("You have been taken off Ratio Watch"), db_string("Congratulations! Feel free to begin downloading again.\n To ensure that you do not get put on ratio watch again, please read the rules located [url=/articles.php?topic=ratio]here[/url].\n"), '');
                echo "Ratio watch off: $UserID\n";
        }
        $DB->set_query_id($UserQuery);
        $Passkeys = $DB->collect('torrent_pass');
        foreach($Passkeys as $Passkey) {
                update_tracker('update_user', array('passkey' => $Passkey, 'can_leech' => '1'));
        }

	
	// Put user on ratio watch if he doesn't meet the standards
	sleep(10);
	$DB->query("SELECT m.ID, m.Downloaded FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
		WHERE m.Uploaded/m.Downloaded < m.RequiredRatio
		AND i.RatioWatchEnds='0000-00-00 00:00:00'
		AND m.Enabled='1'
		AND m.can_leech='1'");
	$OnRatioWatch = $DB->collect('ID');
	
	if(count($OnRatioWatch)>0) {
		$DB->query("UPDATE users_info AS i JOIN users_main AS m ON m.ID=i.UserID
			SET i.RatioWatchEnds='".time_plus(3600*24*14)."',
			i.RatioWatchTimes = i.RatioWatchTimes+1,
			i.RatioWatchDownload = m.Downloaded
			WHERE m.ID IN(".implode(",", $OnRatioWatch).")");
	}
	
	foreach($OnRatioWatch as $UserID) {
		$Cache->begin_transaction('user_info_heavy_'.$UserID);
		$Cache->update_row(false, array('RatioWatchEnds'=>time_plus(3600*24*14),'RatioWatchDownload'=>0));
		$Cache->commit_transaction(0);
		send_pm($UserID, 0, db_string("You have been put on Ratio Watch"), db_string("This happens when your ratio falls below the requirements we have outlined in the rules located [url=/articles.php?topic=ratio]here[/url].\n For information about ratio watch, click the link above."), '');
		echo "Ratio watch on: $UserID\n";
	}

	sleep(5);
	
	//------------- Disable downloading ability of users on ratio watch
	
	
	$UserQuery = $DB->query("SELECT ID, torrent_pass FROM users_info AS i JOIN users_main AS m ON m.ID=i.UserID
		WHERE i.RatioWatchEnds!='0000-00-00 00:00:00'
		AND i.RatioWatchEnds<'$sqltime'
		AND m.Enabled='1'
		AND m.can_leech!='0'");
	
	$UserIDs = $DB->collect('ID');
	if(count($UserIDs) > 0) {
		$DB->query("UPDATE users_info AS i JOIN users_main AS m ON m.ID=i.UserID
			SET 
			m.can_leech='0',
			i.AdminComment=CONCAT('$sqltime - Leeching ability disabled by ratio watch system - required ratio: ', m.RequiredRatio,'

'			, i.AdminComment)
			WHERE m.ID IN(".implode(',',$UserIDs).")");
	}
	
	foreach($UserIDs as $UserID) {
		$Cache->begin_transaction('user_info_heavy_'.$UserID);
		$Cache->update_row(false, array('RatioWatchDownload'=>0, 'CanLeech'=>0));
		$Cache->commit_transaction(0);
		send_pm($UserID, 0, db_string("Your downloading rights have been disabled"), db_string("As you did not raise your ratio in time, your downloading rights have been revoked. You will not be able to download any torrents until your ratio is above your new required ratio."), '');
		echo "Ratio watch disabled: $UserID\n";
	}

	$DB->set_query_id($UserQuery);
	$Passkeys = $DB->collect('torrent_pass');
	foreach($Passkeys as $Passkey) {
		update_tracker('update_user', array('passkey' => $Passkey, 'can_leech' => '0'));
	}
	
	//------------- Disable inactive user accounts --------------------------//
	sleep(5);
	// Send email // TODO: reinstate when we go live
       //  MIFUNE -- lets not keep sending emails to soon to be disabled users with the test sites url in it lol
	$DB->query("SELECT um.Username, um.Email FROM  users_info AS ui JOIN users_main AS um ON um.ID=ui.UserID
		WHERE um.PermissionID IN ('".APPRENTICE."', '".PERV	."')
		AND um.LastAccess<'".time_minus(3600*24*110, true)."'
		AND um.LastAccess>'".time_minus(3600*24*111, true)."'
		AND um.LastAccess!='0000-00-00 00:00:00'
		AND ui.Donor='0'
		AND um.Enabled!='2'");
	while(list($Username, $Email) = $DB->next_record()) {
		$Body = "Hi $Username, \n\nIt has been almost 4 months since you used your account at http://".NONSSL_SITE_URL.". This is an automated email to inform you that your account will be disabled in 10 days if you do not sign in. ";
		send_email($Email, 'Your '.SITE_NAME.' account is about to be disabled', $Body);
	}
       
	$DB->query("SELECT um.ID FROM  users_info AS ui JOIN users_main AS um ON um.ID=ui.UserID
		WHERE um.PermissionID IN ('".APPRENTICE."', '".PERV."')
		AND um.LastAccess<'".time_minus(3600*24*30*4)."' 
		AND um.LastAccess!='0000-00-00 00:00:00'
		AND ui.Donor='0'
		AND um.Enabled!='2'");

	if($DB->record_count() > 0) {
		disable_users($DB->collect('ID'), "Disabled for inactivity.", 3);
	}

	//------------- Disable unconfirmed users ------------------------------//
	sleep(10);
	$DB->query("UPDATE users_info AS ui JOIN users_main AS um ON um.ID=ui.UserID
		SET um.Enabled='2',
		ui.BanDate='$sqltime',
		ui.BanReason='3',
		ui.AdminComment=CONCAT('$sqltime - Disabled for inactivity (never logged in)\n', ui.AdminComment)
		WHERE um.LastAccess='0000-00-00 00:00:00'
		AND ui.JoinDate<'".time_minus(60*60*24*7)."'
		AND um.Enabled!='2'
		");
	$Cache->decrement('stats_user_count',$DB->affected_rows());
	
	echo "disabled unconfirmed\n";
	
	//------------- Demote users --------------------------------------------//
      //  Mifune -  removed upload amount check - this means we can manually promote pervs (who have ratio >0.95) and they wont get auto demoted. its more or less impossible to reduce your upload amount so there is no loss of function anyway
	sleep(10);
	$DB->query('SELECT um.ID FROM users_main AS um WHERE PermissionID IN('.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded/Downloaded < 0.95 '); // OR PermissionID IN('.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded < 25*1024*1024*1024
	
	echo "demoted 1\n";
	
	while(list($UserID) = $DB->next_record()) {
		$Cache->begin_transaction('user_info_'.$UserID);
		$Cache->update_row(false, array('PermissionID'=>PERV));
		$Cache->commit_transaction(2592000);
	}
	$DB->query('UPDATE users_main SET PermissionID='.PERV.' WHERE PermissionID IN('.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded/Downloaded < 0.95 '); // OR PermissionID IN('.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded < 25*1024*1024*1024
	echo "demoted 2\n";
	
	$DB->query('SELECT um.ID FROM users_main AS um WHERE PermissionID IN('.PERV.', '.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded/Downloaded < 0.65');
	echo "demoted 3\n";
	while(list($UserID) = $DB->next_record()) {
		$Cache->begin_transaction('user_info_'.$UserID);
		$Cache->update_row(false, array('PermissionID'=>APPRENTICE));
		$Cache->commit_transaction(2592000);
	}
	$DB->query('UPDATE users_main SET PermissionID='.APPRENTICE.' WHERE PermissionID IN('.PERV.', '.GOOD_PERV.', '.SEXTREME_PERV.', '.SMUT_PEDDLER.') AND Uploaded/Downloaded < 0.65');
	echo "demoted 4\n";

	//------------- Lock old threads ----------------------------------------//
	sleep(10);
	$DB->query("SELECT t.ID
				FROM forums_topics AS t
				JOIN forums AS f ON t.ForumID = f.ID
				WHERE t.IsLocked='0' AND t.IsSticky='0'
				  AND t.LastPostTime<'".time_minus(3600*24*28)."'
				  AND f.AutoLock = '1'");
	$IDs = $DB->collect('ID');
	
	if(count($IDs) > 0) {
		$LockIDs = implode(',', $IDs);
		$DB->query("UPDATE forums_topics SET IsLocked='1' WHERE ID IN($LockIDs)");
		sleep(2);
		//$DB->query("DELETE FROM forums_last_read_topics WHERE TopicID IN($LockIDs)");
	
		foreach($IDs as $ID) {
			$Cache->begin_transaction('thread_'.$ID.'_info');
			$Cache->update_row(false, array('IsLocked'=>'1'));
			$Cache->commit_transaction(3600*24*30);
			$Cache->expire_value('thread_'.$TopicID.'_catalogue_0',3600*24*30);
			$Cache->expire_value('thread_'.$TopicID.'_info',3600*24*30);
		}
	}
	echo "Old threads locked\n";

	//------------- Delete dead torrents ------------------------------------//
	
	sleep(10);
	//remove dead torrents that were never announced to -- XBTT will not delete those with a pid of 0, only those that belong to them (valid pids)
	
	$DB->query("DELETE FROM torrents WHERE flags = 1 AND pid = 0");
	sleep(10);
	

// Lanz: don't remove torrents while we test and move the code to the live server.
// We need to get the seeders back on most torrents before we delete what the
// code would consider inactive torrents. 
    // mifune: re-enabled now we are live
           
	$i = 0;
	$DB->query("SELECT
		t.ID,
		t.GroupID,
		tg.Name,
		t.last_action,
		t.UserID
		FROM torrents AS t
		JOIN torrents_group AS tg ON tg.ID = t.GroupID
		WHERE t.last_action < '".time_minus(3600*24*28)."'
		AND t.last_action != 0
		OR t.Time < '".time_minus(3600*24*2)."'
		AND t.last_action = 0");
	$TorrentIDs = $DB->to_array();
	echo "Found ".count($TorrentIDs)." inactive torrents to be deleted.\n";
	
	$LogEntries = array();
	
	// Exceptions for inactivity deletion
	$InactivityExceptionsMade = array(//UserID => expiry time of exception
		
	);
	foreach ($TorrentIDs as $TorrentID) {
		list($ID, $GroupID, $Name, $LastAction, $UserID) = $TorrentID;
		if (array_key_exists($UserID, $InactivityExceptionsMade) && (time() < $InactivityExceptionsMade[$UserID])) {
			// don't delete the torrent!
			continue;	
		}

		delete_torrent($ID, $GroupID);
		$LogEntries[] = "Torrent ".$ID." (".$Name.") was deleted for inactivity (unseeded)";
		
		if (!array_key_exists($UserID, $DeleteNotes))
				$DeleteNotes[$UserID] = array('Count' => 0, 'Msg' => '');
		
		$DeleteNotes[$UserID]['Msg'] .= "\n$Name";
		$DeleteNotes[$UserID]['Count']++;
		
		++$i;
		if ($i % 500 == 0) {
			echo "$i inactive torrents removed.\n";
		}
	}
	echo "$i torrents deleted for inactivity.\n";
 
	foreach($DeleteNotes as $UserID => $MessageInfo){
		$Singular = ($MessageInfo['Count'] == 1) ? true : false;
		send_pm($UserID,0,db_string($MessageInfo['Count'].' of your torrents '.($Singular?'has':'have').' been deleted for inactivity'), db_string(($Singular?'One':'Some').' of your uploads '.($Singular?'has':'have').' been deleted for being unseeded.  Since '.($Singular?'it':'they').' didn\'t break any rules (we hope), please feel free to re-upload '.($Singular?'it':'them').".\nThe following torrent".($Singular?' was':'s were').' deleted:'.$MessageInfo['Msg']));
	}	
	unset($DeleteNotes);
	
	if(count($LogEntries) > 0) {
		$Values = "('".implode("', '$sqltime'), ('",$LogEntries)."', '$sqltime')";
		$DB->query('INSERT INTO log (Message, Time) VALUES '.$Values);
		echo "\nDeleted $i torrents for inactivity\n";
	}

	// Daily top 10 history.
	$DB->query("INSERT INTO top10_history (Date, Type) VALUES ('$sqltime', 'Daily')");
	$HistoryID = $DB->inserted_id();

	$Top10 = $Cache->get_value('top10tor_day_10');
	if($Top10 === false) {
		$DB->query("SELECT
				t.ID,
				g.ID,
				g.Name,
				g.TagList,
				t.Snatched,
				t.Seeders,
				t.Leechers,
				((t.Size * t.Snatched) + (t.Size * 0.5 * t.Leechers)) AS Data
			FROM torrents AS t
				LEFT JOIN torrents_group AS g ON g.ID = t.GroupID
			WHERE t.Seeders>0
				AND t.Time > ('$sqltime' - INTERVAL 1 DAY)
			ORDER BY (t.Seeders + t.Leechers) DESC
				LIMIT 10;");

		$Top10 = $DB->to_array();
	}

	$i = 1;
	foreach($Top10 as $Torrent) {
		list($TorrentID,$GroupID,$GroupName,$TorrentTags,
                     $Snatched,$Seeders,$Leechers,$Data) = $Torrent;

		$DisplayName.= $GroupName;

		$TitleString = $DisplayName;

		$TagString = str_replace("|", " ", $TorrentTags);

		$DB->query("INSERT INTO top10_history_torrents
			(HistoryID, Rank, TorrentID, TitleString, TagString)
			VALUES
			(".$HistoryID.", ".$i.", ".$TorrentID.", '".db_string($TitleString)."', '".db_string($TagString)."')");
		$i++;
	}

	// Weekly top 10 history.
	// We need to haxxor it to work on a Sunday as we don't have a weekly schedule
	if(date('w') == 0) {
		$DB->query("INSERT INTO top10_history (Date, Type) VALUES ('".$sqltime."', 'Weekly')");
		$HistoryID = $DB->inserted_id();

		$Top10 = $Cache->get_value('top10tor_week_10');
		if($Top10 === false) {
			$DB->query("SELECT
					t.ID,
					g.ID,
					g.Name,
					g.TagList,
					t.Snatched,
					t.Seeders,
					t.Leechers,
					((t.Size * t.Snatched) + (t.Size * 0.5 * t.Leechers)) AS Data
				FROM torrents AS t
					LEFT JOIN torrents_group AS g ON g.ID = t.GroupID
				WHERE t.Seeders>0
					AND t.Time > ('".$sqltime."' - INTERVAL 1 WEEK)
				ORDER BY (t.Seeders + t.Leechers) DESC
					LIMIT 10;");

			$Top10 = $DB->to_array();
		}

		$i = 1;
		foreach($Top10 as $Torrent) {
			list($TorrentID,$GroupID,$GroupName,$TorrentTags,
                             $Snatched,$Seeders,$Leechers,$Data) = $Torrent;
			
			$DisplayName.= $GroupName;

			$TitleString = $DisplayName.' '.$ExtraInfo;

			$TagString = str_replace("|", " ", $TorrentTags);

			$DB->query("INSERT INTO top10_history_torrents
				(HistoryID, Rank, TorrentID, TitleString, TagString)
				VALUES
				(".$HistoryID.", ".$i.", ".$TorrentID.", '".db_string($TitleString)."', '".db_string($TagString)."')");
			$i++;
		}
	
		// Send warnings to uploaders of torrents that will be deleted this week
		$DB->query("SELECT
			t.ID,
			t.GroupID,
			tg.Name,
			t.UserID
			FROM torrents AS t
			JOIN torrents_group AS tg ON tg.ID = t.GroupID
			JOIN users_info AS u ON u.UserID = t.UserID
			WHERE t.last_action < NOW() - INTERVAL 20 DAY
			AND t.last_action != 0
			AND u.UnseededAlerts = '1'
			ORDER BY t.last_action ASC");
		$TorrentIDs = $DB->to_array();
		$TorrentAlerts = array();
		foreach ($TorrentIDs as $TorrentID) {
			list($ID, $GroupID, $Name, $UserID) = $TorrentID;
			
			if (array_key_exists($UserID, $InactivityExceptionsMade) && (time() < $InactivityExceptionsMade[$UserID])) {
				// don't notify exceptions
				continue;	
			}
			
			if (!array_key_exists($UserID, $TorrentAlerts))
				$TorrentAlerts[$UserID] = array('Count' => 0, 'Msg' => '');

                        $TorrentAlerts[$UserID]['Msg'] .= "\n[url=http://".NONSSL_SITE_URL."/torrents.php?torrentid=$ID]".$Name."[/url]";
			$TorrentAlerts[$UserID]['Count']++;
		}
		foreach($TorrentAlerts as $UserID => $MessageInfo){
			send_pm($UserID, 0, db_string('Unseeded torrent notification'), db_string($MessageInfo['Count']." of your upload".($MessageInfo['Count']>1?'s':'')." will be deleted for inactivity soon.  Unseeded torrents are deleted after 4 weeks. If you still have the files, you can seed your uploads by ensuring the torrents are in your client and that they aren't stopped. You can view the time that a torrent has been unseeded by clicking on the torrent description line and looking for the \"Last active\" time. For more information, please go [url=/articles.php?topic=unseeded]here[/url].\n\nThe following torrent".($MessageInfo['Count']>1?'s':'')." will be removed for inactivity:".$MessageInfo['Msg']."\n\nIf you no longer wish to recieve these notifications, please disable them in your profile settings."));
		}
	}
}
/*************************************************************************\
//--------------Run twice per month -------------------------------------//

These functions are twice per month, on the 8th and the 22nd.

\*************************************************************************/

if($BiWeek != next_biweek() || $_GET['runbiweek']) {
	echo "Ran bi-weekly functions\n";

	//------------- Cycle auth keys -----------------------------------------//

	$DB->query("UPDATE users_info
	SET AuthKey =
		MD5(
			CONCAT(
				AuthKey, RAND(), '".db_string(make_secret())."',
				SHA1(
					CONCAT(
						RAND(), RAND(), '".db_string(make_secret())."'
					)
				)
			)
		);"
	);

	//------------- Give out invites! ---------------------------------------//

	/*
	PUs have a cap of 2 invites.  Elites have a cap of 4.
	Every month, on the 8th and the 22nd, each PU/Elite User gets one invite up to their max.

	Then, every month, on the 8th and the 22nd, we give out bonus invites like this:

	Every Power User or Elite whose total invitee ratio is above 0.75 and total invitee upload is over 2 gigs gets one invite.
	Every Elite whose total invitee ratio is above 2.0 and total invitee upload is over 10 gigs gets one more invite.
	Every Elite whose total invitee ratio is above 3.0 and total invitee upload is over 20 gigs gets yet one more invite.

	This cascades, so if you qualify for the last bonus group, you also qualify for the first two and will receive three bonus invites.

	The bonus invites cannot put a user over their cap.
	
	*/

	$DB->query("SELECT ID 
				FROM users_main AS um 
				JOIN users_info AS ui on ui.UserID=um.ID
				WHERE um.Enabled='1' AND ui.DisableInvites = '0'
					AND ((um.PermissionID = ".GOOD_PERV." AND um.Invites < 2) OR (um.PermissionID = ".SEXTREME_PERV." AND um.Invites < 4))");
	$UserIDs = $DB->collect('ID');
	if (count($UserIDs) > 0) {
		foreach($UserIDs as $UserID) {
				$Cache->begin_transaction('user_info_heavy_'.$UserID);
				$Cache->update_row(false, array('Invites' => '+1'));
				$Cache->commit_transaction(0);
		}
		$DB->query("UPDATE users_main SET Invites=Invites+1 WHERE ID IN (".implode(',',$UserIDs).")");
	}

	$BonusReqs = array(
		array(0.75, 2*1024*1024*1024),
		array(2.0, 10*1024*1024*1024),
		array(3.0, 20*1024*1024*1024));
	
	// Since MySQL doesn't like subselecting from the target table during an update, we must create a temporary table.

	$DB->query("CREATE TEMPORARY TABLE temp_sections_schedule_index
		SELECT SUM(Uploaded) AS Upload,SUM(Downloaded) AS Download,Inviter
		FROM users_main AS um JOIN users_info AS ui ON ui.UserID=um.ID
		GROUP BY Inviter");
	
	foreach ($BonusReqs as $BonusReq) {
		list($Ratio, $Upload) = $BonusReq;
		$DB->query("SELECT ID
					FROM users_main AS um 
					JOIN users_info AS ui ON ui.UserID=um.ID
					JOIN temp_sections_schedule_index AS u ON u.Inviter = um.ID
					WHERE u.Upload>$Upload AND u.Upload/u.Download>$Ratio 
						AND um.Enabled = '1' AND ui.DisableInvites = '0' 
						AND ((um.PermissionID = ".GOOD_PERV." AND um.Invites < 2) OR (um.PermissionID = ".SEXTREME_PERV." AND um.Invites < 4))");
		$UserIDs = $DB->collect('ID');
		if (count($UserIDs) > 0) {
			foreach($UserIDs as $UserID) {
					$Cache->begin_transaction('user_info_heavy_'.$UserID);
					$Cache->update_row(false, array('Invites' => '+1'));
					$Cache->commit_transaction(0);
			}
			$DB->query("UPDATE users_main SET Invites=Invites+1 WHERE ID IN (".implode(',',$UserIDs).")");
		}
	}
	
	if($BiWeek == 8) {
		$DB->query("TRUNCATE TABLE top_snatchers;");
		$DB->query("INSERT INTO top_snatchers (UserID) SELECT uid FROM xbt_snatched GROUP BY uid ORDER BY COUNT(uid) DESC LIMIT 100;");
	}
}




//---  moved this run every 15 mins section to the end as if xbt_peers_history gets too big (>~2.6 million? records on our server...) 
//          it can screw the scheduler

//------------ Remove unwatched and unwanted speed records

// as we are deleting way way more than keeping, and to avoid exceeding lockrow size in innoDB we do it another way:
$DB->query("DROP TABLE IF EXISTS temp_copy"); // jsut in case!
$DB->query("CREATE TABLE `temp_copy` (  
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
  KEY `fid` (`fid`),
  KEY `upspeed` (`upspeed`),
  KEY `mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8"); 

// insert the records we want to keep into the temp table
$DB->query("INSERT INTO temp_copy (uid, downloaded, remaining, uploaded, upspeed, downspeed, timespent, peer_id, ip, fid, mtime)
                    SELECT x.uid, x.downloaded, x.remaining, x.uploaded, x.upspeed, x.downspeed, x.timespent, x.peer_id, x.ip, x.fid, x.mtime 
                      FROM xbt_peers_history AS x
                 LEFT JOIN users_watch_list AS uw ON uw.UserID=x.uid
                 LEFT JOIN torrents_watch_list AS tw ON tw.TorrentID=x.fid
                     WHERE uw.UserID IS NOT NULL
                        OR tw.TorrentID IS NOT NULL
                        OR x.upspeed >= '$KeepSpeed'
                        OR x.mtime>'".($nowtime - ( $DeleteRecordsMins * 60))."'" );

//Use RENAME TABLE to atomically move the original table out of the way and rename the copy to the original name:
$DB->query("RENAME TABLE xbt_peers_history TO temp_old, temp_copy TO xbt_peers_history");
//Drop the original table:
$DB->query("DROP TABLE temp_old");

 

echo "-------------------------\n\n";
if (check_perms('admin_schedule')) {	
	echo '</pre>';
	show_footer();
}
?>
