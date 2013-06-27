
<?
echo "<pre>";

$sqltime= sqltime();

    // ---------- remove old requests (and return bounties) -------------
    
    // return bounties for each voter
    $DB->query("SELECT r.ID, r.Title, v.UserID, v.Bounty
                  FROM requests as r JOIN requests_votes as v ON v.RequestID=r.ID 
                 WHERE TorrentID='0' AND TimeAdded < '".time_minus(3600*24*90)."'" );
    
	$RemoveBounties = $DB->to_array();
    $RemoveRequestIDs = array();
    
        echo "remove bounties: \n" .print_r( $RemoveBounties, true);
        
        
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

        /*
        // delete the requests
        $DB->query("DELETE r, v, t, c
                      FROM requests as r 
                 LEFT JOIN requests_votes as v ON r.ID=v.RequestID 
                 LEFT JOIN requests_tags AS t ON r.ID=t.RequestID 
                 LEFT JOIN requests_comments AS c ON r.ID=c.RequestID
                     WHERE r.ID IN(".implode(",", $RemoveRequestIDs).")"); 
*/
        
        //log and update sphinx (sphinx call must be done after requests are deleted)    
        foreach($RemoveRequests as $Request) {
            list($RequestID, $Title, $NumUsers, $Bounty, $GroupID) = $Request;

            write_log("Request $RequestID ($Title) expired - returned total of ". get_size($Bounty)." to $NumUsers users");

            $Cache->delete_value('request_votes_'.$RequestID);
            if ($GroupID) {
                $Cache->delete_value('requests_group_'.$GroupID);
            }
            //update_sphinx_requests($RequestID);

        }
        
        echo "\n\nremove request IDs: \n" .print_r( $RemoveRequestIDs, true);
    }
    
echo "</pre>";
    
?>