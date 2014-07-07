<?php
//******************************************************************************//
//--------------- Delete request -----------------------------------------------//

authorize();

$RequestID = $_POST['id'];
if(!is_number($RequestID)) {
	error(0);
}

$DB->query("SELECT UserID,
			Title,
			GroupID
			FROM requests
			WHERE ID = ".$RequestID);
list($UserID, $Title, $GroupID) = $DB->next_record();

if(!check_perms('site_moderate_requests')) {
	error(403);
}

$FullName = $Title;

// Delete request, votes and tags
$DB->query("DELETE FROM requests WHERE ID='$RequestID'");
$DB->query("DELETE FROM requests_votes WHERE RequestID='$RequestID'");
$DB->query("DELETE FROM requests_tags WHERE RequestID='$RequestID'");

if($UserID != $LoggedUser['ID']) {
	send_pm($UserID, 0, db_string("A request you created has been deleted"), db_string("The request '".$FullName."' was deleted by [url=http://".NONSSL_SITE_URL."/user.php?id=".$LoggedUser['ID']."]".$LoggedUser['Username']."[/url] for the reason: ".$_POST['reason']));
}

write_log("Request $RequestID ($FullName) was deleted by user ".$LoggedUser['ID']." (".$LoggedUser['Username'].") for the reason: ".$_POST['reason']);

$Cache->delete_value('request_'.$RequestID);
$Cache->delete_value('request_votes_'.$RequestID);
if ($GroupID) {
	$Cache->delete_value('requests_group_'.$GroupID);
}
update_sphinx_requests($RequestID);

header('Location: requests.php');
