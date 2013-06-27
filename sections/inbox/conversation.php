<?
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

$ConvID = $_GET['id'];
if(!$ConvID || !is_number($ConvID)) { error(404); }



$UserID = $LoggedUser['ID'];
$DB->query("SELECT InInbox, InSentbox FROM pm_conversations_users WHERE UserID='$UserID' AND ConvID='$ConvID'");
if($DB->record_count() == 0) {
	error(403);
}
list($InInbox, $InSentbox) = $DB->next_record();

if (!$InInbox && !$InSentbox) {
	error(404);
}

// Get information on the conversation
$DB->query("SELECT
	c.Subject,
	cu.Sticky,
	cu.UnRead,
	cu.ForwardedTo,
	um.Username
	FROM pm_conversations AS c
	JOIN pm_conversations_users AS cu ON c.ID=cu.ConvID
	LEFT JOIN users_main AS um ON um.ID=cu.ForwardedTo
	WHERE c.ID='$ConvID' AND UserID='$UserID'");
list($Subject, $Sticky, $UnRead, $ForwardedID, $ForwardedName) = $DB->next_record();

/*
$DB->query("SELECT UserID, Username, PermissionID, CustomPermissions, Enabled, Donor, Warned, Title
	FROM pm_messages AS pm
	JOIN users_info AS ui ON ui.UserID=pm.SenderID
	JOIN users_main AS um ON um.ID=pm.SenderID
	WHERE pm.ConvID='$ConvID'");
$UsersInMessages = $DB->to_array(); */

$DB->query("SELECT pm.UserID, Username, PermissionID, GroupPermissionID, CustomPermissions, Enabled, Donor, Warned, Title
	FROM pm_conversations_users AS pm
	JOIN users_info AS ui ON ui.UserID=pm.UserID
	JOIN users_main AS um ON um.ID=pm.UserID
	WHERE pm.ConvID='$ConvID'");
$UsersInMessages = $DB->to_array();
//$SenderID=-1;
foreach($UsersInMessages as $UserM){
    list($PMUserID, $Username, $PermissionID, $GroupPermID, $CustomPermissions, $Enabled, $Donor, $Warned, $Title) = $UserM;
	$PMUserID = (int)$PMUserID;
    //if($SenderID==-1)$SenderID=$PMUserID;//kind of hacky way of getting the sender without waiting for loop thru messages later
	$Users[$PMUserID]['UserStr'] = format_username($PMUserID, $Username, $Donor, $Warned, $Enabled, $PermissionID, $Title, true, $GroupPermID);
	$Users[$PMUserID]['Username'] = $Username;
	$Users[$PMUserID]['AdvTags'] = get_permissions_advtags($PMUserID, $CustomPermissions);
}
$Users[0]['UserStr'] = 'System'; // in case it's a message from the system
$Users[0]['Username'] = 'System';
$Users[0]['AdvTags'] = true;


if($UnRead=='1') {

	$DB->query("UPDATE pm_conversations_users SET UnRead='0' WHERE ConvID='$ConvID' AND UserID='$UserID'");
	// Clear the caches of the inbox and sentbox
	$Cache->decrement('inbox_new_'.$UserID);
}



show_header('View conversation '.$Subject, 'comments,inbox,bbcode');

?>
<div class="thin">
	<h2><?=$Subject.($ForwardedID > 0 ? ' (Forwarded to '.$ForwardedName.')':'')?></h2>
	<div class="linkbox">
		<a href="inbox.php">[Back to inbox]</a>
	</div>
<?
// Get messages
$DB->query("SELECT SentDate, SenderID, Body, ID FROM pm_messages AS m WHERE ConvID='$ConvID' ORDER BY ID");
$Messages = $DB->to_array();

//while(list($SentDate, $SenderID, $Body, $MessageID) = $DB->next_record()) { 
    
foreach ($Messages as $Message) {
    list($SentDate, $SenderID, $Body, $MessageID) = $Message;

    if (!$donedetails) {
        $donedetails=true;
        $CSenderID = $SenderID;
        //if ($SenderID==0) $ReplyID = $LoggedUser['ID'];

        $DB->query("SELECT UserID FROM pm_conversations_users 
                     WHERE UserID!='$CSenderID' AND ConvID='$ConvID' AND (ForwardedTo=0 OR ForwardedTo=UserID)
                    ORDER BY SentDate Desc LIMIT 1");
        list($ReplyID) = $DB->next_record();

?>  
        <div class="head">conversation details</div>
        <div class="box pad vertical_space colhead">
            started by <strong><?=$Users[$SenderID]['Username']?></strong> <?=time_diff($SentDate); ?>
            <span style="float:right">to <strong><?=$Users[$ReplyID]['Username']?></strong></span>&nbsp;
        </div>
<?
    }
?>  
    <div class="head">
        <?=$Users[$SenderID]['UserStr'].' '.time_diff($SentDate);
        if($SenderID!=0) { 
        ?>  - <a href="#quickpost" onclick="Quote('<?=$MessageID?>','','<?=$Users[$SenderID]['Username']?>');">[Quote]</a>	
        <? }  
        ?>  - <a href="#quickpost" title="Forward just this message" onclick="Foward_To('<?=$MessageID?>');">[Forward message]</a>  
    </div>
	<div class="box vertical_space">
		<div class="body" id="message<?=$MessageID?>">
			<?=$Text->full_format($Body, $Users[(int)$SenderID]['AdvTags'])?>
		</div>
	</div>
<?
}

//$DB->query("SELECT UserID FROM pm_conversations_users WHERE UserID!='$LoggedUser[ID]' AND ConvID='$ConvID' AND (ForwardedTo=0 OR ForwardedTo=UserID)");
//$ReceiverIDs = $DB->collect('UserID');
//if(!empty($ReceiverIDs) && (empty($LoggedUser['DisablePM']) || array_intersect($ReceiverIDs, array_keys($StaffIDs)))) {

//$DB->query("SELECT SenderID FROM pm_messages WHERE ConvID='$ConvID'");
//$DB->query("SELECT SenderID FROM pm_messages WHERE ConvID='$ConvID' ORDER BY ID LIMIT 1");

$DB->query("SELECT UserID FROM pm_conversations_users 
             WHERE UserID!='$LoggedUser[ID]' AND ConvID='$ConvID' AND (ForwardedTo=0 OR ForwardedTo=UserID)
            ORDER BY SentDate Desc LIMIT 1");
list($ReplyID) = $DB->next_record();


if(!empty($ReplyID) && $ReplyID!=0 && $CSenderID!=0 && ( empty($LoggedUser['DisablePM']) || array_key_exists($ReplyID, $StaffIDs) ) ) {
?>
	<div class="head">Reply to <?=$Users[$ReplyID]['Username']?></div>
	<div class="box pad">
            <form action="inbox.php" method="post" id="messageform">
			<input type="hidden" name="action" value="takecompose" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
			<input type="hidden" name="toid" value="<?=$ReplyID?>" />
			<input type="hidden" name="convid" value="<?=$ConvID?>" />
            <? $Text->display_bbcode_assistant("quickpost", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); ?>
			<textarea id="quickpost" name="body" class="long" rows="10"></textarea> <br />
			<div id="preview" class="box vertical_space body hidden"></div>
			<div id="buttons" class="center">
				<input type="button" value="Preview" onclick="Quick_Preview();" /> 
				<input type="submit" value="Send reply to <?=$Users[$ReplyID]['Username']?>" />
			</div>
            </form>
      </div>
<?
}


//$DB->query("SELECT SupportFor FROM users_info WHERE UserID = ".$LoggedUser['ID']);
//list($FLS) = $DB->next_record();
//if((check_perms('users_mod') || $FLS != "") && (!$ForwardedID || $ForwardedID == $LoggedUser['ID'])) {
?>
	<div class="head">Forward as new PM (takes you to a new compose message page)</div>
	<div class="box pad rowa">
            <form id="forwardform" action="inbox.php" method="post">
			<input type="hidden" name="action" value="forward" />
			<input type="hidden" name="convid" value="<?=$ConvID?>" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
			<input type="hidden" id="forwardmessage" name="forwardmessage" value="conversation" />
            
            <input type="radio" id="forwardto" name="forwardto" value="user" checked="checked" />
			<label for="receivername">Forward to user:</label>
            <input id="receivername" type="text" name="receivername" value="" size="20" onfocus="javascript: $('#forwardto').raw().checked=true"/>
            &nbsp;&nbsp;&nbsp;
            <input type="radio" id="forwardtostaff" name="forwardto" value="staff" />
			<label for="receiverid">Forward to staff member:</label>
			<select id="receiverid" name="receiverid" onfocus="javascript: $('#forwardtostaff').raw().checked=true">
<?
	foreach($StaffIDs as $StaffID => $StaffName) {
		if($StaffID == $LoggedUser['ID'] || in_array($StaffID, $ReceiverIDs)) {
			continue;
		}
?>
				<option value="<?=$StaffID?>"><?=$StaffName?>&nbsp;&nbsp;</option>
<?
	}
?>
			</select>
            &nbsp;&nbsp;&nbsp;
			<input type="button" onclick="Foward_To('conversation');" value="Forward entire conversation" />
            </form>
     </div>
<?
//}

?>
	<div class="head">Manage conversation</div>
	<div class="box pad rowa">
        <form action="inbox.php" method="post">
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="convid" value="<?=$ConvID?>" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />

			<table width="100%" class="noborder">
				<tr class="rowa">
					<td class="center" width="33%"><label for="sticky">Sticky</label>
						<input type="checkbox" id="sticky" name="sticky"<? if($Sticky) { echo ' checked="checked"'; } ?> />
					</td>
					<td class="center" width="33%"><label for="mark_unread">Mark as unread</label>
						<input type="checkbox" id="mark_unread" name="mark_unread" />
					</td>
					<td class="center" width="33%"><label for="delete">Delete conversation</label>
						<input type="checkbox" id="delete" name="delete" />
					</td>
				</tr>
				<tr class="rowa">
					<td class="center" colspan="3"><input type="submit" value="Manage conversation" /></td>
				</tr>
			</table>
        </form>
	</div>
<?

//And we're done!
?>
</div>
<?
show_footer();
?>
