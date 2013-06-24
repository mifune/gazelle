<?
//******************************************************************************//
//--------------- Vote on a request --------------------------------------------//
//This page is ajax!

header('Content-Type: application/json; charset=utf-8');
                    
if(!check_perms('site_vote'))  error(403, true);

authorize();

if(empty($_GET['id']) || !is_number($_GET['id'])) error(0, true); 


$RequestID = (int)$_GET['id'];

if(empty($_GET['amount']) || !is_number($_GET['amount']) || $_GET['amount'] < $MinimumVote) {
	$Amount = $MinimumVote;
} else {
	$Amount = $_GET['amount'];
}

$Bounty = $Amount;

$DB->query('SELECT TorrentID FROM requests WHERE ID='.$RequestID);
list($Filled) = $DB->next_record();
if($Filled>0) error("This torrent is already filled!", true);


if($LoggedUser['BytesUploaded'] < $Amount) {
    
    echo json_encode(array( 'bankrupt', $Bounty, 0, 0, false ));
    
} else {
     
	// Create vote!
	$DB->query("INSERT IGNORE INTO requests_votes
					(RequestID, UserID, Bounty)
				VALUES
					( $RequestID , $LoggedUser[ID] , $Bounty )");
	
	if($DB->affected_rows() < 1) {
		//Insert failed, probably a dupe vote, just increase their bounty.
			$DB->query("UPDATE requests_votes
						SET Bounty = (Bounty + $Bounty )
						WHERE UserID = $LoggedUser[ID] AND RequestID = $RequestID");
		$voteaction = 'dupe';
	} else {
        $voteaction = 'success';
    }

	

	$DB->query("UPDATE requests SET LastVote = NOW() WHERE ID = ".$RequestID);
	
	$Cache->delete_value('request_'.$RequestID);
	$Cache->delete_value('request_votes_'.$RequestID);

	// Subtract amount from user
	$DB->query("UPDATE users_main SET Uploaded = (Uploaded - ".$Amount.") WHERE ID = ".$LoggedUser['ID']);
    write_user_log($LoggedUser['ID'], "Removed -". get_size($Amount). " for vote on [url=/requests.php?action=view&id={$RequestID}]Request {$RequestID}[/url] ");
    
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);

	update_sphinx_requests($RequestID);
    
    $RequestVotes = get_votes_array($RequestID);
    
    echo json_encode(array( $voteaction, $Bounty, 
                            $RequestVotes['TotalBounty'], count($RequestVotes['Voters']), get_votes_html( $RequestVotes ) ) );
 
}



/*
if(!check_perms('site_vote')) {
	error(403);
}
authorize();

if(empty($_GET['id']) || !is_number($_GET['id'])) { 
	error(0); 
}
$RequestID = $_GET['id'];

if(empty($_GET['amount']) || !is_number($_GET['amount']) || $_GET['amount'] < $MinimumVote) {
	$Amount = $MinimumVote;
} else {
	$Amount = $_GET['amount'];
}

$Bounty = $Amount;
$DB->query('SELECT TorrentID FROM requests WHERE ID='.$RequestID);
list($Filled) = $DB->next_record();

if($LoggedUser['BytesUploaded'] >= $Amount && $Filled == 0){
	
	// Create vote!
	$DB->query("INSERT IGNORE INTO requests_votes
					(RequestID, UserID, Bounty)
				VALUES
					(".$RequestID.", ".$LoggedUser['ID'].", ".$Bounty.")");
	
	if($DB->affected_rows() < 1) {
		//Insert failed, probably a dupe vote, just increase their bounty.
			$DB->query("UPDATE requests_votes
						SET Bounty = (Bounty + ".$Bounty.")
						WHERE
							UserID = ".$LoggedUser['ID']."
							AND RequestID = ".$RequestID);
		echo 'dupe';
	}
	$DB->query("UPDATE requests SET LastVote = NOW() WHERE ID = ".$RequestID);
	
	$Cache->delete_value('request_'.$RequestID);
	$Cache->delete_value('request_votes_'.$RequestID);

	// Subtract amount from user
	$DB->query("UPDATE users_main SET Uploaded = (Uploaded - ".$Amount.") WHERE ID = ".$LoggedUser['ID']);
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);

	update_sphinx_requests($RequestID);
	echo 'success';
} elseif($LoggedUser['BytesUploaded'] < $Amount) {
	echo 'bankrupt';
}  */

?>
