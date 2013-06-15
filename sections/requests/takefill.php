<?
//******************************************************************************//
//--------------- Fill a request -----------------------------------------------//

$RequestID = $_REQUEST['requestid'];
if(!is_number($RequestID)) {
	error(0);
}

authorize();

//VALIDATION
if(!empty($_GET['torrentid']) && is_number($_GET['torrentid'])) {
	$TorID = $_GET['torrentid'];
} else {
	if(empty($_POST['link'])) {
		$Err = "You forgot to supply a link to the filling torrent";
	} else {
		$Link = $_POST['link'];
		if(preg_match("/".TORRENT_REGEX."/i", $Link, $Matches) < 1) {
			$Err = "Your link didn't seem to be a valid torrent link";
		} else {
			$GroupID = $Matches[3];
		}
	}
	
	if(!empty($Err)) {
		error($Err);
	}
	
	if(!$GroupID || !is_number($GroupID)) {
		error(404);
	}
}

$Where = $TorID ? "t.ID = $TorrentID" : "tg.ID = $GroupID";

//Torrent exists, check it's applicable
$DB->query("SELECT 
                    t.ID,
                    t.UserID,
                    t.Time,
                    tg.NewCategoryID				
            FROM torrents AS t
                    LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID
            WHERE $Where 
            LIMIT 1");


if($DB->record_count() < 1) {
	error(404);
}
list($TorrentID, $UploaderID, $UploadTime, $TorrentCategoryID) = $DB->next_record();

$FillerID = $LoggedUser['ID'];
$FillerUsername = $LoggedUser['Username'];

if(!empty($_POST['user']) && check_perms('site_moderate_requests')) {
	$FillerUsername = $_POST['user'];
	$DB->query("SELECT ID FROM users_main WHERE Username LIKE '".db_string($FillerUsername)."'");
	if($DB->record_count() < 1) {
		$Err = "No such user to fill for!";
	} else {
		list($FillerID) = $DB->next_record();
	}
}

if(time_ago($UploadTime) < 3600 && $UploaderID != $FillerID && !check_perms('site_moderate_requests')) {
	$Err = "There is a one hour grace period for new uploads, to allow the torrent's uploader to fill the request";
}



$DB->query("SELECT Title, UserID, TorrentID, CategoryID
              FROM requests WHERE ID = ".$RequestID);
list($Title, $RequesterID, $OldTorrentID, $RequestCategoryID) = $DB->next_record();


if(!empty($OldTorrentID)) {
	$Err = "This request has already been filled";
}
/*  // mifune: removing category match
if($RequestCategoryID != 0 && $TorrentCategoryID != $RequestCategoryID) {
	$Err = "This torrent is of a different category than the request";
} */

// Fill request
if(!empty($Err)) {
	error($Err);
}

//We're all good! Fill!
$DB->query("UPDATE requests SET
				FillerID = ".$FillerID.",
				TorrentID = ".$TorrentID.",
				TimeFilled = '".sqltime()."'
			WHERE ID = ".$RequestID);	

$FullName = $Title;

$DB->query("SELECT UserID FROM requests_votes WHERE RequestID = ".$RequestID);
$UserIDs = $DB->to_array();
foreach ($UserIDs as $User) {
	list($VoterID) = $User;
	send_pm($VoterID, 0, db_string("The request '".$FullName."' has been filled"), db_string("One of your requests - [url=/requests.php?action=view&id=".$RequestID."]".$FullName."[/url] - has been filled. You can view it at [url=/torrents.php?torrentid=".$TorrentID."]http://".NONSSL_SITE_URL."/torrents.php?torrentid=".$TorrentID."[/url]"), '');
}

$RequestVotes = get_votes_array($RequestID);
write_log("Request ".$RequestID." (".$FullName.") was filled by user ".$FillerID." (".$FillerUsername.") with the torrent ".$TorrentID.", for a ".get_size($RequestVotes['TotalBounty'])." bounty.");

// Give bounty
$DB->query("UPDATE users_main
			SET Uploaded = (Uploaded + ".$RequestVotes['TotalBounty'].") 
			WHERE ID = ".$FillerID);

write_user_log($FillerID, "Added +". get_size($RequestVotes['TotalBounty']). " for filling [url=/requests.php?action=view&id={$RequestID}]Request $RequestID ({$Title})[/url] ");
    


$Cache->delete_value('user_stats_'.$FillerID);
$Cache->delete_value('request_'.$RequestID);
$Cache->delete_value('requests_torrent_'.$TorrentID);
if ($GroupID) {
	$Cache->delete_value('requests_group_'.$GroupID);
}

$SS->UpdateAttributes('requests', array('torrentid','fillerid'), array($RequestID => array((int)$TorrentID,(int)$FillerID)));
update_sphinx_requests($RequestID);

header('Location: requests.php?action=view&id='.$RequestID);
?>
