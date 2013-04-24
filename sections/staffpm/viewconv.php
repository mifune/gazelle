<?
// FLS+Staff
if ($IsFLS) {
    include(SERVER_ROOT.'/sections/staffpm/functions.php');
    list($NumMy, $NumUnanswered, $NumOpen) = get_num_staff_pms($LoggedUser['ID'], $LoggedUser['Class']);
}
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

if ($ConvID = (int)$_GET['id']) {
	// Get conversation info
	$DB->query("SELECT Subject, UserID, Level, AssignedToUser, Unread, Status, ResolverID FROM staff_pm_conversations WHERE ID=$ConvID");
	list($Subject, $UserID, $Level, $AssignedToUser, $Unread, $Status, $ResolverID) = $DB->next_record();
 
	if (!(($UserID == $LoggedUser['ID']) || ($AssignedToUser == $LoggedUser['ID']) || (($Level > 0 && $Level <= $LoggedUser['Class']) || ($Level == 0 && $IsFLS)))) {
	// User is trying to view someone else's conversation
		error(403);
	}
	// User is trying to view their own unread conversation, set it to read
	if ($UserID == $LoggedUser['ID'] && $Unread) {
		$DB->query("UPDATE staff_pm_conversations SET Unread=false WHERE ID=$ConvID");
		// Clear cache for user
		$Cache->delete_value('staff_pm_new_'.$LoggedUser['ID']);
	}

	show_header('Staff PM', 'staffpm,bbcode,jquery');

	$OwnerInfo = user_info($UserID);
	$UserStr = format_username($UserID, $OwnerInfo['Username'], $OwnerInfo['Donor'], $OwnerInfo['Warned'], $OwnerInfo['Enabled'], $OwnerInfo['PermissionID'], $OwnerInfo['Title'], true);
      $OwnerID = $UserID;
      if($ResolverID) {
          	$ResolverInfo = user_info($ResolverID);
            $ResolverStr = format_username($ResolverID, $ResolverInfo['Username'], $ResolverInfo['Donor'], $ResolverInfo['Warned'], $ResolverInfo['Enabled'], $ResolverInfo['PermissionID']);
	}
	// Get assigned
	if ($AssignedToUser == '') { // Assigned to class
            $Assigned = ($Level == 0) ? "First Line Support" : $ClassLevels[$Level]['Name'];
            // No + on Sysops
		if ($Assigned != 'Sysop') { $Assigned .= "+"; }
      } else {  // Assigned to user
            $AssignInfo = user_info($AssignedToUser);
		$Assigned = format_username($AssignedToUser, $AssignInfo['Username'], $AssignInfo['Donor'], $AssignInfo['Warned'], $AssignInfo['Enabled'], $AssignInfo['PermissionID']);
      }
?>
<div class="thin">
	<h2>Staff PM - <?=display_str($Subject)?></h2>
	<div class="linkbox">
          
<? 	if ($IsStaff) { ?>
		[ &nbsp;<a href="staffpm.php?view=my">My unanswered<?=$NumMy>0?" ($NumMy)":''?></a>&nbsp; ] &nbsp; 
<? 	} 
	// FLS/Staff
	if ($IsFLS) {
?>
		[ &nbsp;<a href="staffpm.php?view=unanswered">All unanswered<?=$NumUnanswered>0?" ($NumUnanswered)":''?></a>&nbsp; ] &nbsp; 
		[ &nbsp;<a href="staffpm.php?view=open">Open<?=$NumOpen>0?" ($NumOpen)":''?></a>&nbsp; ] &nbsp; 
		[ &nbsp;<a href="staffpm.php?view=resolved">Resolved</a>&nbsp; ]  &nbsp; 
            [ &nbsp;<a href="staffpm.php?action=responses&convid=<?=$ConvID?>">Common Answers</a>&nbsp; ]
<?           // User
	} else {
?>
		[ &nbsp;<a href="staffpm.php">Back to inbox</a>&nbsp; ]
<?
	}
?>
		<br />
		<br />
	</div>
	 
<?
	// Get messages
	$StaffPMs = $DB->query("SELECT UserID, SentDate, Message FROM staff_pm_messages WHERE ConvID=$ConvID ORDER BY SentDate");
      
	while(list($UserID, $SentDate, $Message) = $DB->next_record()) {
		// Set user string
		if ($UserID == $OwnerID) {
			// User, use prepared string
			$UserString = $UserStr;
		} else {
			// Staff/FLS
			$UserInfo = user_info($UserID);
			$UserString = format_username($UserID, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'], $UserInfo['PermissionID'], $UserInfo['Title'], true);
            }
            // determine if conversation was started by user or not (checks first record for userID)
            if (!isset($UserInitiated)) {
                $UserInitiated = $UserID == $OwnerID;  
?> 
                <div class="head">
                    Status: <?=$Status; if($ResolverStr && $Status=='Resolved' ) echo " by $ResolverStr"; ?> 
<?                  if($UserInitiated){ ?>
                        <span style="float:right"><em>Assigned to: <?=$Assigned?></em></span>    
<?                  }  ?> 
                </div>
                <div class="box pad vertical_space colhead">
                    <span style="float:right"> 
<?                      $SenderString = format_username($UserID, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'], $UserInfo['PermissionID'], false, true);
                        echo "sent by $SenderString&nbsp;&nbsp;"; ?>
                    </span>
                    Sent to  <?=$UserInitiated?'<strong>Staff</strong>':$UserStr;?> 
                    <span style="float:right;margin-right:30%"> 
                        Status: <?=$Status; ?>
                    </span>
                </div>
                <br/>
<?             
            } 
?>
            <div class="head">
                <?=$UserString;?>
                <span class="small" style="float:right">
                <?=time_diff($SentDate, 2, true);?>
                </span>
            </div>
		<div class="box vertical_space">
			<div class="body"><?=$Text->full_format($Message, get_permissions_advtags($UserID))?></div>
		</div>
		<div align="center" style="display: none"></div>
<?
		$DB->set_query_id($StaffPMs);
	}

	// Common responses
	if ($IsFLS && $Status != 'Resolved') {
?>
		<div id="common_answers" class="hidden">
			<div class="box vertical_space">
				<div class="head">
					<strong>Preview</strong>
				</div>
				<div id="common_answers_body" class="body">Select an answer from the dropdown to view it.</div>
			</div>
			<br />
			<div class="center">
				<select id="common_answers_select" onChange="UpdateMessage();">
					<option id="first_common_response">Select a message</option>
<?
		// List common responses
		$DB->query("SELECT ID, Name FROM staff_pm_responses");
		while(list($ID, $Name) = $DB->next_record()) {
?>
					<option value="<?=$ID?>"><?=$Name?></option>
<?		} ?>
				</select>
				<input type="button" value="Set message" onClick="SetMessage();" />
				<input type="button" value="Create new / Edit" onClick="location.href='staffpm.php?action=responses&convid=<?=$ConvID?>'" />
			</div>
		</div>
<?	}

	// Ajax assign response div
	if ($IsStaff) { ?>
            <div class="messagecontainer"><div id="ajax_message" class="hidden center messagebar"></div></div>
<?	}

	// Replybox and buttons
?> 
		<div class="head">
                <strong>Reply</strong> <?
                if (!$IsFLS) {
                    if($Status != 'Resolved') { 
                        if ($UserInitiated) echo " &nbsp; <em>(click resolve to close the conversation if you are happy with the answer given)</em>";
                    }  else { 
                        echo " &nbsp; <em>(click unresolve to reopen the conversation)</em>"; 
                    }
                }
                ?>
		</div>
		<div class="box pad">
			<div id="preview" class="box pad hidden"></div>
			<div id="buttons" class="center">
				<form action="staffpm.php" method="post" class="staffpm" id="messageform">
					<input type="hidden" name="action" value="takepost" />
					<input type="hidden" name="convid" value="<?=$ConvID?>" id="convid" />
<?              if ($Status != 'Resolved') {    ?>
                       <? $Text->display_bbcode_assistant("quickpost", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); ?>
					<textarea id="quickpost" name="message" class="long" rows="10"></textarea> 
                              <br />
					<input type="button" id="previewbtn" value="Preview" style="margin-right: 40px;" onclick="PreviewMessage();" />
<?              }   ?>
<?
	// Assign to
	if ($IsStaff) {
		// Staff assign dropdown
?>
					<select id="assign_to" name="assign">
						<optgroup label="User classes">
<?		// FLS "class"
		$Selected = (!$AssignedToUser && $Level == 0) ? ' selected="selected"' : '';
?>
							<option value="class_0"<?=$Selected?>>First Line Support</option>
<?		// Staff classes
		foreach ($ClassLevels as $Class) {
			// Create one <option> for each staff user class  >= 650
			if ($Class['Level'] >= 500) {
				$Selected = (!$AssignedToUser && ($Level == $Class['Level'])) ? ' selected="selected"' : '';
?>
							<option value="class_<?=$Class['Level']?>"<?=$Selected?>><?=$Class['Name']?></option>
<?			}
		} ?>
						</optgroup>
						<optgroup label="Staff">
<?		// Staff members
		$DB->query("
			SELECT
				m.ID,
				m.Username
			FROM permissions as p
			JOIN users_main as m ON m.PermissionID=p.ID
			WHERE p.DisplayStaff='1'
			ORDER BY p.Level DESC, m.Username ASC"
		);
		while(list($ID, $Name) = $DB->next_record()) {
			// Create one <option> for each staff member
			$Selected = ($AssignedToUser == $ID) ? ' selected="selected"' : '';
?>
							<option value="user_<?=$ID?>"<?=$Selected?>><?=$Name?></option>
<?		} ?>
						</optgroup>
						<optgroup label="First Line Support">
<?
		// FLS users
		$DB->query("
			SELECT
				m.ID,
				m.Username
			FROM users_info as i
			JOIN users_main as m ON m.ID=i.UserID
			JOIN permissions as p ON p.ID=m.PermissionID
			WHERE p.DisplayStaff!='1' AND i.SupportFor!=''
			ORDER BY m.Username ASC
		");
		while(list($ID, $Name) = $DB->next_record()) {
			// Create one <option> for each FLS user
			$Selected = ($AssignedToUser == $ID) ? ' selected="selected"' : '';
?>
							<option value="user_<?=$ID?>"<?=$Selected?>><?=$Name?></option>
<?		} ?>
						</optgroup>
					</select>
					<input type="button" onClick="Assign();" value="Assign" />
<?	} elseif ($IsFLS) {	// FLS assign button ?>
					<input type="button" value="Assign to staff" onClick="location.href='staffpm.php?action=assign&to=staff&convid=<?=$ConvID?>';" />
					<input type="button" value="Assign to admins" onClick="location.href='staffpm.php?action=assign&to=admin&convid=<?=$ConvID?>';" />
<?	}

	if ($Status != 'Resolved') {
                  if ($UserInitiated || $IsFLS) { // as staff can now start a staff - user conversation check to see if user should be able to resolve  ?>
					<input type="button" value="Resolve" onClick="location.href='staffpm.php?action=resolve&id=<?=$ConvID?>';" />
<?			} 
                  if ($IsFLS) {  //Moved by request ?>
					<input type="button" value="Common answers" onClick="$('#common_answers').toggle();" />
<?			} ?>
					<input type="submit" value="Send message" />
<?	} else { 
            // if ($UserInitiated || $IsFLS) {  ?> 
					<input type="button" value="Unresolve" onClick="location.href='staffpm.php?action=unresolve&id=<?=$ConvID?>&return=1';" />
<?			// }  
 	}
	?>
				</form>
                <?
                if (check_perms('users_give_donor')) { ?>
        <br/> 
        <form action="donate.php" method="post">
            <input type="hidden" name="action" value="submit_donate_manual" />
            <input type="hidden" name="convid" value="<?=$ConvID?>" />
            <input type="hidden" name="userid" value="<?=$OwnerID?>" />
            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
            make <?=format_username($OwnerID, $OwnerInfo['Username'])?> a donor:
            &nbsp; Amount: <strong style="font-size:19px;">&euro; </strong><input type="text" name="amount" value="" /> &nbsp; &nbsp; &nbsp;
            <input type="submit" name="donategb" value="donate for -GB" /> 
            <input type="submit" name="donatelove" value="donate for love" />
        </form>
					<!--<input type="button" value="Make Donor" onClick="location.href='staffpm.php?action=make_donor&id=<?=$ConvID?>';" />-->
<?	} 
                
                
                ?>
			</div>
		</div>
	 
</div>
<?

	show_footer();
} else {
	// No id
	header('Location: staffpm.php');
}
