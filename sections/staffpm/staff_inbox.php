<?
include(SERVER_ROOT.'/sections/staffpm/functions.php');

show_header('Staff Inbox');

$View = display_str($_GET['view']);
$UserLevel = $LoggedUser['Class'];

list($NumMy, $NumUnanswered, $NumOpen) = get_num_staff_pms($LoggedUser['ID'], $UserLevel);

// Setup for current view mode
$SortStr = "IF(AssignedToUser = ".$LoggedUser['ID'].",0,1) ASC, ";
switch ($View) {
	case 'open':
		$ViewString = "All open";
		$WhereCondition = "WHERE (Level <= $UserLevel OR AssignedToUser='".$LoggedUser['ID']."') AND Status IN ('Open', 'Unanswered')";
		$SortStr = '';
		break;
	case 'resolved':
		$ViewString = "Resolved";
		$WhereCondition = "WHERE (Level <= $UserLevel OR AssignedToUser='".$LoggedUser['ID']."') AND Status='Resolved'";
		$SortStr = '';
		break;
	case 'my':
		$ViewString = "My unanswered";
		$WhereCondition = "WHERE (Level = $UserLevel OR AssignedToUser='".$LoggedUser['ID']."') AND Status='Unanswered'";
		break;
	case 'unanswered':
	default:
		$ViewString = "All unanswered";
		$WhereCondition = "WHERE (Level <= $UserLevel OR AssignedToUser='".$LoggedUser['ID']."') AND Status='Unanswered'";
		break;
}

list($Page,$Limit) = page_limit(MESSAGES_PER_PAGE);
// Get messages
$StaffPMs = $DB->query("
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		Subject,
		UserID,
		Status,
		Level,
		AssignedToUser,
		Date,
		Unread,
		ResolverID
	FROM staff_pm_conversations
	$WhereCondition
	ORDER BY $SortStr Status DESC, Date DESC
	LIMIT $Limit
");

$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();
$DB->set_query_id($StaffPMs);

$CurURL = get_url();
if(empty($CurURL)) {
	$CurURL = "staffpm.php?";
} else {
	$CurURL = "staffpm.php?".$CurURL."&";
}
$Pages=get_pages($Page,$NumResults,MESSAGES_PER_PAGE,9);

$Row = 'a';

// Start page
?>
<div class="thin">
	<div class="linkbox">
<? 	if ($IsStaff) { ?>
		[ &nbsp;<a href="staffpm.php?view=my">My unanswered<?=$NumMy>0?" ($NumMy)":''?></a>&nbsp; ] &nbsp; 
<? 	} ?>
		[ &nbsp;<a href="staffpm.php?view=unanswered">All unanswered<?=$NumUnanswered>0?" ($NumUnanswered)":''?></a>&nbsp; ] &nbsp; 
		[ &nbsp;<a href="staffpm.php?view=open">Open<?=$NumOpen>0?" ($NumOpen)":''?></a>&nbsp; ] &nbsp; 
		[ &nbsp;<a href="staffpm.php?view=resolved">Resolved</a>&nbsp; ] &nbsp; 
		[ &nbsp;<a href="staffpm.php?action=responses">Common Answers</a>&nbsp; ]
		<br />
		<br />
		<?=$Pages?>
	</div>
	<div class="head"><?=$ViewString?> Staff PMs</div>    
	<div class="box pad" id="inbox">
<?

if ($DB->record_count() == 0) {
	// No messages
?>
		<h2>No messages</h2>
<?

} else {
	// Messages, draw table
	if ($ViewString != 'Resolved' && $IsStaff) {
		// Open multiresolve form
?>
		<form method="post" action="staffpm.php" id="messageform" onsubmit="return anyChecks('messageform')">
			<input type="hidden" name="action" value="multiresolve" />
			<input type="hidden" name="view" value="<?=strtolower($View)?>" />
<?
	}

	// Table head
?>
			<table>
				<tr class="colhead">
<? 				if ($ViewString != 'Resolved' && $IsStaff) { ?>
					<td width="10"><input type="checkbox" onclick="toggleChecks('messageform',this)" /></td>
<? 				} ?>
					<td>Subject</td>
					<td width="20%">User</td>
					<td width="18%">Date</td>
					<td width="18%">Assigned to</td>
<?				if ($ViewString == 'Resolved') { ?>
					<td width="18%">Resolved by</td>
<?				} else { ?>
                              <td width="8%">Status</td>
<?				}  ?>
				</tr>
<?

	// List messages
	while(list($ID, $Subject, $UserID, $Status, $Level, $AssignedToUser, $Date, $Unread, $ResolverID) = $DB->next_record()) {
		$Row = ($Row === 'a') ? 'b' : 'a';
		$RowClass = 'row'.$Row;

		$UserInfo = user_info($UserID);
		$UserStr = format_username($UserID, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'], $UserInfo['PermissionID']);

		// Get assigned
		if ($AssignedToUser == '') {
			// Assigned to class
			$Assigned = ($Level == 0) ? "First Line Support" : $ClassLevels[$Level]['Name'];
			// No + on Sysops
			if ($Assigned != 'Sysop') { $Assigned .= "+"; }

		} else {
			// Assigned to user
			$UserInfo = user_info($AssignedToUser);
			$Assigned = format_username($AssignedToUser, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'], $UserInfo['PermissionID']);

		}

		// Get resolver
		if ($ViewString == 'Resolved') {
			$UserInfo = user_info($ResolverID);
			$ResolverStr = format_username($ResolverID, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'], $UserInfo['PermissionID']);
		}

		// Table row
?>
				<tr class="<?=$RowClass?>">
<? 				if ($ViewString != 'Resolved' && $IsStaff) { ?>
					<td class="center"><input type="checkbox" name="id[]" value="<?=$ID?>" /></td>
<? 				} ?>
					<td><a href="staffpm.php?action=viewconv&amp;id=<?=$ID?>"><?=display_str($Subject)?></a></td>
					<td><?=$UserStr?></td>
					<td><?=time_diff($Date, 2, true)?></td>
					<td><?=$Assigned?></td>
<?				if ($ViewString == 'Resolved') { ?>
					<td><?=$ResolverStr?></td>
<?				} else { ?>
                              <td><?=$Status?></td>
<?				} ?>
				</tr>
<?

		$DB->set_query_id($StaffPMs);
	}

	// Close table and multiresolve form
?>
			</table>
<? 		if ($ViewString != 'Resolved' && $IsStaff) { ?>
			<input type="submit" value="Resolve selected" />
<?		} ?>
		</form>
<?

}

?>
	</div>
	<div class="linkbox">
		<?=$Pages?>
	</div>
</div>
<?

show_footer();

?>
