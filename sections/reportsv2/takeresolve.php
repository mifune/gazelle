<?
/*
 * This is the backend of the AJAXy reports resolve (When you press the shiny submit button). 
 * This page shouldn't output anything except in error, if you do want output, it will be put
 * straight into the table where the report used to be. Currently output is only given when
 * a collision occurs or a POST attack is detected.
 */

if(!check_perms('admin_reports')) {
	error(403);
}
authorize();


//Don't escape: Log message, Admin message
$Escaped = db_array($_POST, array('log_message','admin_message', 'raw_name'));

//If we're here from the delete torrent page instead of the reports page.
if(!isset($Escaped['from_delete']) || $Escaped['from_delete']==0) {
	$Report = true;
} else if(!is_number($Escaped['from_delete'])) {
	echo 'Hax occured in from_delete';
} else {
	$Report = false;
}

$PMMessage = $_POST['uploader_pm'];

if(is_number($Escaped['reportid'])) {
	$ReportID = $Escaped['reportid'];
} else {
	echo 'Hax occured in the reportid';
	die();
}

if($Escaped['pm_type'] != 'Uploader') {
	$Escaped['uploader_pm'] = '';
}

$UploaderID = (int)$Escaped['uploaderid'];
if(!is_number($UploaderID)) {
	echo 'Hax occuring on the uploaderid';
	die();
}

if (isset($Escaped['reporterid'])){
    $ReporterID = (int)$Escaped['reporterid'];
    if(!is_number($ReporterID)) {
          echo 'Hax occuring on the reporterid';
          die();
    }
}

$Warning = (int)$Escaped['warning'];
if(!is_number($Warning)) {
	echo 'Hax occuring on the warning';
	die();
}

$TorrentID = $Escaped['torrentid'];
$RawName = $Escaped['raw_name'];

// GroupID is only used to delete the torrent group cache key.
$DB->query("SELECT GroupID FROM torrents WHERE ID='$TorrentID'");
list($GroupID) = $DB->next_record();

if(($Escaped['resolve_type'] == "manual" || $Escaped['resolve_type'] == "dismiss" ) && $Report) {
	if($Escaped['comment']) {
		$Comment = $Escaped['comment'];
	} else {
		if($Escaped['resolve_type'] == "manual") {
			$Comment = "Report was resolved manually";
		} elseif($Escaped['resolve_type'] == "dismiss") {
			 $Comment = "Report was dismissed as invalid";
		}
	}
	
	$DB->query("UPDATE reportsv2 SET
	Status='Resolved',
	LastChangeTime='".sqltime()."',
	ModComment = '".$Comment."',
	ResolverID='".$LoggedUser['ID']."'
	WHERE ID='".$ReportID."'
	AND Status <> 'Resolved'");
	
	if($DB->affected_rows() > 0) {
		$Cache->delete_value('num_torrent_reportsv2');
		$Cache->delete_value('reports_torrent_'.$TorrentID);
                $Cache->delete_value('torrent_group_'.$GroupID);
	} else {
	//Someone beat us to it. Inform the staffer.
?>
        <table cellpadding="5">
            <tr>
                <td>
                    <a href="reportsv2.php?view=report&amp;id=<?=$ReportID?>">Somebody has already resolved this report</a>
                    <input type="button" value="Clear" onclick="ClearReport(<?=$ReportID?>);" />
                </td>
            </tr>
        </table>
<?
	}
	die();
}

if(!isset($Escaped['resolve_type'])) {
	echo 'No resolve type';
	die();
} else if (array_key_exists($_POST['resolve_type'], $Types)) {
	$ResolveType = $Types[$_POST['resolve_type']];
} else {
	//There was a type but it wasn't an option!
	echo "HAX (Invalid Resolve Type)";
	die();
}


$DB->query("SELECT ID FROM torrents WHERE ID = ".$TorrentID);
$TorrentExists = ($DB->record_count() > 0);
if(!$TorrentExists) {
	$DB->query("UPDATE reportsv2 
		SET Status='Resolved',
		LastChangeTime='".sqltime()."',
		ResolverID='".$LoggedUser['ID']."',
		ModComment='Report already dealt with (Torrent deleted)'
	WHERE ID=".$ReportID);

	$Cache->decrement('num_torrent_reportsv2');
}

if($Report) {
	//Resolve with a parallel check
	$DB->query("UPDATE reportsv2 
		SET Status='Resolved',
			LastChangeTime='".sqltime()."',
			ResolverID='".$LoggedUser['ID']."'
		WHERE ID=".$ReportID."
			AND Status <> 'Resolved'");
}

//See if it we managed to resolve
if($DB->affected_rows() > 0 || !$Report) {
	//We did, lets do all our shit
	if($Report) { $Cache->decrement('num_torrent_reportsv2'); }

	
	if(isset($Escaped['upload'])) {
		$Upload = true;
	} else {
		$Upload = false;
	}

	if(isset($Escaped['bounty'])) {
		$Bounty = true;
	} else {
		$Bounty = false;
	}

	if($_POST['resolve_type'] == "tags_lots") {
		$DB->query("INSERT IGNORE INTO torrents_bad_tags (TorrentID, UserID, TimeAdded) VALUES (".$TorrentID.", ".$LoggedUser['ID']." , '".sqltime()."')");
		$DB->query("SELECT GroupID FROM torrents WHERE ID = ".$TorrentID);
		list($GroupID) = $DB->next_record();
		$Cache->delete_value('torrents_details_'.$GroupID);
		$SendPM = true;
	}

	if($_POST['resolve_type'] == "folders_bad") {
		$DB->query("INSERT IGNORE INTO torrents_bad_folders (TorrentID, UserID, TimeAdded) VALUES (".$TorrentID.", ".$LoggedUser['ID'].", '".sqltime()."')");
		$DB->query("SELECT GroupID FROM torrents WHERE ID = ".$TorrentID);
		list($GroupID) = $DB->next_record();
		$Cache->delete_value('torrents_details_'.$GroupID);
		$SendPM = true;
	}
	if($_POST['resolve_type'] == "filename") {
		$DB->query("INSERT IGNORE INTO torrents_bad_files (TorrentID, UserID, TimeAdded) VALUES (".$TorrentID.", ".$LoggedUser['ID'].", '".sqltime()."')");
		$DB->query("SELECT GroupID FROM torrents WHERE ID = ".$TorrentID);
		list($GroupID) = $DB->next_record();
		$Cache->delete_value('torrents_details_'.$GroupID);
		$SendPM = true;
	}
	
	//Log and delete
	if(isset($Escaped['delete']) && check_perms('users_mod')) {
		$DB->query("SELECT Username FROM users_main WHERE ID = ".$UploaderID);
		list($UpUsername) = $DB->next_record();
		$Log = "Torrent ".$TorrentID." (".$RawName.") uploaded by ".$UpUsername." was deleted by ".$LoggedUser['Username'];
		$Log .= ($Escaped['resolve_type'] == 'custom' ? "" : " for the reason: ".$ResolveType['title'].".");
		if(isset($Escaped['log_message']) && $Escaped['log_message'] != "") {
			$Log .= " ( ".$Escaped['log_message']." )";
		}
		$DB->query("SELECT GroupID FROM torrents WHERE ID = ".$TorrentID);
		list($GroupID) = $DB->next_record();
        
        if($ResolveType['title']=='Dupe' && isset($Escaped['extras_id'])) {
            //------ if deleting a dupe pm peers with the duped torrents id
			$ExtraIDs = explode(" ", $Escaped['extras_id']);
            foreach($ExtraIDs as $ExtraID){
                if(!is_number($ExtraID)) error(0);
            }
            $ExtraIDs = implode(',', $ExtraIDs);
            
            $DB->query("SELECT DISTINCT uid FROM xbt_snatched WHERE fid = '$TorrentID'
                        UNION
                        SELECT DISTINCT uid FROM xbt_files_users WHERE fid = '$TorrentID'");
            
            if ($DB->record_count()>0) {
                $Peers = $DB->collect('uid');
                $Message = "Torrent ".$TorrentID." (".$RawName.") was deleted for being a dupe.[br][br]";
                $Message .= "The torrent it was duping was:";
                //$DB->query("SELECT ID, Name FROM torrents_group WHERE ID IN ($ExtraIDs)");
				$DB->query("SELECT tg.ID, tg.Name, t.Time, t.Size, t.UserID, um.Username
							  FROM torrents AS t JOIN torrents_group AS tg ON tg.ID=t.GroupID
							  LEFT JOIN users_main AS um ON um.ID=t.UserID
							 WHERE tg.ID IN ($ExtraIDs) 
							 ORDER BY t.Time DESC");
                while(list($xID, $xName, $xTime, $xSize, $xUserID, $xUsername) = $DB->next_record()) {
					$Message .= "[br][url=/torrents.php?id=$xID]{$xName}[/url] (". get_size($xSize).") uploaded by [url=/user.php?id=$xUserID]{$xUsername}[/url] " .time_diff($xTime,2,false,false);
                }
                $Message .= "[br][br]You should be able to join the torrent already here by grabbing its torrent file and doing a force recheck in your torrent client.";
                send_pm($Peers, 0, db_string('A torrent you were a peer on was deleted'), db_string($Message)); 
            }
 
        }
        
		delete_torrent($TorrentID);
		write_log($Log);
		$Log = "deleted torrent for the reason: ".$ResolveType['title'].". ( ".$Escaped['log_message']." )";
		write_group_log($GroupID, $TorrentID, $LoggedUser['ID'], $Log, 0);
	} else {
		$Log = "No log message (Torrent wasn't deleted)";
	}
	
	//Warnings / remove upload
	if($Upload) {
		$Cache->begin_transaction('user_info_heavy_'.$UploaderID);
		$Cache->update_row(false, array('DisableUpload' => '1'));
		$Cache->commit_transaction(0);
	
		$DB->query("UPDATE users_info SET
			DisableUpload='1'
			WHERE UserID=".$UploaderID);
	}
	
      if($Bounty && $ReporterID){
          $Bounty = (int)$ResolveType['resolve_options']['bounty'];
          if ($Bounty>0){
              
                $SET = "m.Credits=(m.Credits+$Bounty)";
                $Summary = sqltime()." - User received a bounty payment of $Bounty credits.";	
                $SET .=",i.AdminComment=CONCAT_WS( '\n', '".db_string($Summary)."', i.AdminComment)";
                $Summary = sqltime()." | +$Bounty credits | You got a bounty payment.";
                $SET .=",i.BonusLog=CONCAT_WS( '\n', '".db_string($Summary)."', i.BonusLog)";
                
                $DB->query("UPDATE users_main AS m JOIN users_info AS i ON m.ID=i.UserID SET $SET WHERE m.ID='$ReporterID'");
                $Cache->delete_value('users_stats_'.$ReporterID);
            
                $Body = "Thank-you for your {$ResolveType['title']} report re: [url=http://".NONSSL_SITE_URL."/torrents.php?torrentid=$TorrentID]{$RawName}[/url]\n\nYou received a bounty payment of $Bounty credits.";
               
                send_pm($ReporterID, 0, "Received Bounty Payment", $Body);
          }
      }
      
	if($Warning > 0) {
		$WarnLength = $Warning * (7*24*60*60); 
		$Reason = "Uploader of torrent (".$TorrentID.") ".$RawName." which was resolved with the preset: ".$ResolveType['title'].".";
		if($Escaped['admin_message']) {
			$Reason .= " (".$Escaped['admin_message'].").";
		}
		if($Upload) {
			$Reason .= " (Upload privileges Removed).";
		}

		warn_user($UploaderID, $WarnLength, $Reason);
	} else {
		//This is a bitch for people that don't warn but do other things, it makes me sad.
		$AdminComment = "";
		if($Upload) {
			//They removed upload
			$AdminComment .= "Upload privileges removed by ".$LoggedUser['Username'];
			$AdminComment .= "\nReason: Uploader of torrent (".$TorrentID.") ".db_string($RawName)." which was resolved with the preset: ".$ResolveType['title'].". (Report ID: $ReportID)";
		}
		if($Escaped['admin_message']) {
			//They did nothing of note, but still want to mark it (Or upload and mark)
			$AdminComment .= " (".$Escaped['admin_message'].")";
		}
		if($AdminComment) {
			$AdminComment = date("Y-m-d").' - '.$AdminComment."\n\n";
				
			$DB->query("UPDATE users_info SET
				AdminComment=CONCAT('".db_string($AdminComment)."',AdminComment)
				WHERE UserID='".db_string($UploaderID)."'");
		}
	}
	
	//PM
	if($Escaped['uploader_pm'] || $Warning > 0 || isset($Escaped['delete']) || $SendPM) {
		if(isset($Escaped['delete'])) {
			$PM = "[url=http://".NONSSL_SITE_URL."/torrents.php?torrentid=".$TorrentID."]Your above torrent[/url] was reported and has been deleted.\n\n";
		} else {
			$PM = "[url=http://".NONSSL_SITE_URL."/torrents.php?torrentid=".$TorrentID."]Your above torrent[/url] was reported but not deleted.\n\n";
		}
		
		$Preset = $ResolveType['resolve_options']['pm'];
		
		if($Preset != "") {
			 $PM .= "Reason: ".$Preset;
		}
		
		if($Warning > 0) {
			$PM .= "\nThis has resulted in a [url=/articles.php?topic=rules]$Warning week warning.[/url]\n";	
		}
		
		if($Upload) {
			$PM .= "This has ".($Warning > 0 ? 'also ' : '')."resulted in you losing your upload privileges.";
		}

		if($Log) {
			$PM = $PM."\nLog Message: ".$Log."\n";
		}
		
		if($Escaped['uploader_pm']) {
			$PM .= "\nMessage from ".$LoggedUser['Username'].": ".$PMMessage;	
		}
		
		$PM .= "\n\nReport was handled by [user]".$LoggedUser['Username']."[/user].";
		
		send_pm($UploaderID, 0, db_string($Escaped['raw_name']), db_string($PM));
	}

	$Cache->delete_value('reports_torrent_'.$TorrentID);
	$Cache->delete_value('torrent_group_'.$GroupID);
        
	//Now we've done everything, update the DB with values
	if($Report) { 
		$DB->query("UPDATE reportsv2 SET
		Type = '".$Escaped['resolve_type']."',
		LogMessage='".db_string($Log)."',
		ModComment='".$Escaped['comment']."'
		WHERE ID=".$ReportID);
	}
} else {
	//Someone beat us to it. Inform the staffer.
?>
	<table cellpadding="5">
		<tr>
			<td>
				<a href="reportsv2.php?view=report&amp;id=<?=$ReportID?>">Somebody has already resolved this report</a>
				<input type="button" value="Clear" onclick="ClearReport(<?=$ReportID?>);" />
			</td>
		</tr>
	</table>
<?
}
?>
