<?
//******************************************************************************//
//--------------- Take unfill request ------------------------------------------//

authorize();

$RequestID = $_POST['id'];
if(!is_number($RequestID)){
	error(0);
}

$DB->query("SELECT
		r.UserID, 
		r.FillerID, 
		r.Title,
		u.Uploaded,
		r.TorrentID,
		r.GroupID
	FROM requests AS r 
		LEFT JOIN users_main AS u ON u.ID=FillerID
	WHERE r.ID= ".$RequestID);
list($UserID, $FillerID, $Title, $Uploaded, $TorrentID, $GroupID) = $DB->next_record();

if((($LoggedUser['ID'] != $UserID && $LoggedUser['ID'] != $FillerID) && !check_perms('site_moderate_requests')) || $FillerID == 0) {
		error(403);
}

// Unfill
$DB->query("UPDATE requests SET
			TorrentID = 0,
			FillerID = 0,
			TimeFilled = '0000-00-00 00:00:00',
			Visible = 1
			WHERE ID = ".$RequestID);

$FullName = $Title;

$RequestVotes = get_votes_array($RequestID);

if ($RequestVotes['TotalBounty'] > $Uploaded) {
	// If we can't take it all out of upload, zero that out and add whatever is left as download.
	$DB->query("UPDATE users_main SET Uploaded = 0 WHERE ID = ".$FillerID);
	$DB->query("UPDATE users_main SET Downloaded = Downloaded + ".($RequestVotes['TotalBounty']-$Uploaded)." WHERE ID = ".$FillerID);
    
    write_user_log($FillerID, "Removed -". get_size($Uploaded). " from Download AND added +". get_size(($RequestVotes['TotalBounty']-$Uploaded)). " to Upload because [url=/requests.php?action=view&id={$RequestID}]Request $RequestID ({$Title})[/url] was unfilled.");
} else {
	$DB->query("UPDATE users_main SET Uploaded = Uploaded - ".$RequestVotes['TotalBounty']." WHERE ID = ".$FillerID);
    
    write_user_log($FillerID, "Removed -". get_size($RequestVotes['TotalBounty']). " because [url=/requests.php?action=view&id={$RequestID}]Request $RequestID ({$Title})[/url] was unfilled.");
}

send_pm($FillerID, 0, db_string("A request you filled has been unfilled"), db_string("The request '[url=http://".NONSSL_SITE_URL."/requests.php?action=view&id=".$RequestID."]".$FullName."[/url]' was unfilled by [url=http://".NONSSL_SITE_URL."/user.php?id=".$LoggedUser['ID']."]".$LoggedUser['Username']."[/url] for the reason: ".$_POST['reason']));

$Cache->delete_value('user_stats_'.$FillerID);

if($UserID != $LoggedUser['ID']) {
	send_pm($UserID, 0, db_string("A request you created has been unfilled"), db_string("The request '[url=http://".NONSSL_SITE_URL."/requests.php?action=view&id=".$RequestID."]".$FullName."[/url]' was unfilled by [url=http://".NONSSL_SITE_URL."/user.php?id=".$LoggedUser['ID']."]".$LoggedUser['Username']."[/url] for the reason: ".$_POST['reason']));
}

write_log("Request $RequestID ($FullName), with a ".get_size($RequestVotes['TotalBounty'])." bounty, was un-filled by user ".$LoggedUser['ID']." (".$LoggedUser['Username'].") for the reason: ".$_POST['reason']);

$Cache->delete_value('request_'.$RequestID);
$Cache->delete_value('requests_torrent_'.$TorrentID);
if ($GroupID) {
	$Cache->delete_value('requests_group_'.$GroupID);
}

update_sphinx_requests($RequestID);

header('Location: requests.php?action=view&id='.$RequestID);
?>
