<?

//******************************************************************************//
//----------------- Take request -----------------------------------------------//

authorize();

if(!check_perms('site_submit_requests')) error(403);

if($_POST['action'] != "takenew" &&  $_POST['action'] != "takeedit") {
	error(0);
}

include(SERVER_ROOT . '/sections/torrents/functions.php');
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

$NewRequest = ($_POST['action'] == "takenew");

if(!$NewRequest) {
	$ReturnEdit = true;
}

if($NewRequest) {
	if(!check_perms('site_submit_requests') || $LoggedUser['BytesUploaded'] < 250*1024*1024){
		error(403);
	}
} else {
	$RequestID = $_POST['requestid'];
	if(!is_number($RequestID)) {
		error(0);
	}
	
	$Request = get_requests(array($RequestID));
	$Request = $Request['matches'][$RequestID];
	if(empty($Request)) {
		error(404);
	}
	
	list($RequestID, $RequestorID, $RequestorName, $TimeAdded, $LastVote, $CategoryID, $Title, $Image, $Description,
	     $FillerID, $FillerName, $TorrentID, $TimeFilled, $GroupID) = $Request;
	$VoteArray = get_votes_array($RequestID);
	$VoteCount = count($VoteArray['Voters']);
	
	$IsFilled = !empty($TorrentID);
	
	$ProjectCanEdit = (check_perms('project_team') && !$IsFilled && (($CategoryID == 0)));
	$CanEdit = ((!$IsFilled && $LoggedUser['ID'] == $RequestorID && $VoteCount < 2) || $ProjectCanEdit || check_perms('site_moderate_requests'));
	
	if(!$CanEdit) {
		error(403);
	}
}

// Validate
if(empty($_POST['category'])) {
	error(0);
}

$CategoryID = $_POST['category'];

if(empty($CategoryID)) {
	error(0);
}

if(empty($_POST['title'])) {
	$Err = "You forgot to enter the title!";
} else {
	$Title = trim($_POST['title']);
}

if(empty($_POST['tags'])) {
	$Err = "You forgot to enter any tags!";
} else {
	$Tags = trim($_POST['tags']);
}

if($NewRequest) {
	if(empty($_POST['amount'])) {
		$Err = "You forgot to enter any bounty!";
	} else {
		$Bounty = trim($_POST['amount']);
		if(!is_number($Bounty)) {
			$Err = "Your entered bounty is not a number";
		} elseif($Bounty < 100*1024*1024) {
			$Err = "Minumum bounty is 100MB";
		}
		$Bytes = $Bounty; //From MB to B
	}
}

if(empty($_POST['image'])) {
	$Image = "";
} else {
    
      $Result = validate_imageurl($_POST['image'], 12, 255, get_whitelist_regex());
      if($Result!==TRUE) $Err = $Result;
      else $Image = trim($_POST['image']);
      
    /*
	if(preg_match("/".IMAGE_REGEX."/", trim($_POST['image'])) > 0) {
			$Image = trim($_POST['image']);
	} else {
		$Err = display_str($_POST['image'])." does not appear to be a valid link to an image.";
	} */
}

if(empty($_POST['description'])) {
	$Err = "You forgot to enter any description!";
} else {
	$Description = trim($_POST['description']);
}


$Text->validate_bbcode($_POST['description'],  get_permissions_advtags($LoggedUser['ID']));
      

if(!empty($Err)) {
	error($Err);
	$Div = $_POST['unit'] == 'mb' ? 1024*1024 : 1024*1024*1024;
	$Bounty /= $Div;
	include(SERVER_ROOT.'/sections/requests/new_edit.php');
	die();
}

if($NewRequest) {
        $DB->query("INSERT INTO requests (     
                            UserID, TimeAdded, LastVote, CategoryID, Title, Image, Description, Visible)
                    VALUES
                            (".$LoggedUser['ID'].", '".sqltime()."', '".sqltime()."',  ".$CategoryID.", '".db_string($Title)."', '".db_string($Image)."', '".db_string($Description)."', '1')");

        $RequestID = $DB->inserted_id();
} else {
        $DB->query("UPDATE requests 
        SET CategoryID = ".$CategoryID.",
                Title = '".db_string($Title)."', 
                Image = '".db_string($Image)."',
                Description = '".db_string($Description)."'
        WHERE ID = ".$RequestID);
}

//Tags
if(!$NewRequest) {
	$DB->query("DELETE FROM requests_tags WHERE RequestID = ".$RequestID);
}


$Tags = explode(' ', strtolower($NewCategories[$CategoryID]['tag']." ".$Tags));

$TagsAdded=array();
foreach ($Tags as $Tag) {
        $Tag = strtolower(trim($Tag,'.')); // trim dots from the beginning and end
        if (!is_valid_tag($Tag) || !check_tag_input($Tag)) continue;
        $Tag = get_tag_synonym($Tag);
        if (!empty($Tag)) {
            if (!in_array($Tag, $TagsAdded)){ // and to create new tags as Uses=1 which seems more correct
                $TagsAdded[] = $Tag;
                $DB->query("INSERT INTO tags
                            (Name, UserID, Uses) VALUES
                            ('$Tag', $LoggedUser[ID], 1)
                            ON DUPLICATE KEY UPDATE Uses=Uses+1;");
                $TagID = $DB->inserted_id();
	
                $DB->query("INSERT IGNORE INTO requests_tags
					(TagID, RequestID) VALUES 
					($TagID, $RequestID)");
            }
        }
}
// replace the original tag array with corrected tags
$Tags = $TagsAdded;


/*
$Tags = cleanup_tags($Tags);
$Tags = array_unique(explode(' ', $Tags));
foreach($Tags as $Index => $Tag) {
	$Tag = sanitize_tag($Tag);
	$Tags[$Index] = $Tag; //For announce
	
	$DB->query("INSERT INTO tags 
					(Name, UserID)
				VALUES 
					('".$Tag."', ".$LoggedUser['ID'].") 
				ON DUPLICATE KEY UPDATE Uses=Uses+1");
	
	$TagID = $DB->inserted_id();
	
	$DB->query("INSERT IGNORE INTO requests_tags
					(TagID, RequestID)
				VALUES 
					(".$TagID.", ".$RequestID.")");
}  */

if($NewRequest) {
	//Remove the bounty and create the vote
	$DB->query("INSERT INTO requests_votes 
					(RequestID, UserID, Bounty)
				VALUES
					(".$RequestID.", ".$LoggedUser['ID'].", ".$Bytes.")");
	
	$DB->query("UPDATE users_main SET Uploaded = (Uploaded - ".$Bytes.") WHERE ID = ".$LoggedUser['ID']);
	$Cache->delete_value('user_stats_'.$LoggedUser['ID']);

	
    $Announce = "'".$Title."' - http://".NONSSL_SITE_URL."/requests.php?action=view&id=".$RequestID." - ".implode(" ", $Tags);
        
	send_irc('PRIVMSG #'.NONSSL_SITE_URL.'-requests :'.$Announce);
    
    write_log("Request $RequestID ($Title) created with " . get_size($Bytes). " bounty by ". $LoggedUser['Username']);
	
} else {
	$Cache->delete_value('request_'.$RequestID);
}

        

update_sphinx_requests($RequestID);

header('Location: requests.php?action=view&id='.$RequestID);
?>
