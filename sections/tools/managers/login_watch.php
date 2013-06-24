<?
if(!check_perms('admin_login_watch')) { error(403); }


// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = "desc") {
    global $OrderBy, $OrderWay;
    if ($SortKey == $OrderBy) {
        if ($OrderWay == "desc") {
            $NewWay = "asc";
        } else {
            $NewWay = "desc";
        }
    } else {
        $NewWay = $DefaultWay;
    }

    return "tools.php?action=login_watch&amp;order_way=" . $NewWay . "&amp;order_by=" . $SortKey . "&amp;" . get_url(array('order_way', 'order_by'));
}






if(isset($_POST['submit']) && isset($_POST['id']) && $_POST['submit'] == 'Unban' && is_number($_POST['id'])){
	authorize();
	$DB->query('DELETE FROM login_attempts WHERE ID='.$_POST['id']);
}



if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}

if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('IP', 'Username', 'LastAttempt', 'Attempts', 'BannedUntil', 'Bans'))) {
    $_GET['order_by'] = 'LastAttempt';
    $OrderBy = 'LastAttempt'; 
} else {
    $OrderBy = $_GET['order_by'];
}


list($Page,$Limit) = page_limit(50);

$DB->query("SELECT SQL_CALC_FOUND_ROWS
                   l.ID,
                   l.IP,
                   l.UserID,
                   l.LastAttempt,
                   l.Attempts,
                   l.BannedUntil,
                   l.Bans,
                   m.Username,
                   m.PermissionID,
                   m.Enabled,
                   i.Donor,
                   i.Warned
              FROM login_attempts AS l
         LEFT JOIN users_main AS m ON m.ID=l.UserID
         LEFT JOIN users_info AS i ON i.UserID=l.UserID
             WHERE l.Attempts>0
          ORDER BY $OrderBy $OrderWay
             LIMIT $Limit");

	//WHERE l.BannedUntil > "'.sqltime().'"
$FailedLogins = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 
 
$Pages=get_pages($Page,$NumResults,50,9);


show_header('Login Watch');

?>
<div class="thin">
<h2>Login Watch Management</h2>

	<div class="linkbox"><?=$Pages?></div>
     
<table width="100%">
	<tr class="colhead">
        <td><a href="<?=header_link('IP') ?>">IP</a></td>
        <td><a href="<?=header_link('Username') ?>">User</a></td>
        <td><a href="<?=header_link('Attempts') ?>">Attempts</a></td>
        <td><a href="<?=header_link('LastAttempt') ?>">Last Attempt</a></td>
        <td><a href="<?=header_link('Bans') ?>">Bans</a></td>
        <td><a href="<?=header_link('BannedUntil') ?>">Remaining</a></td>
		<td style="width:160px">Submit</td> 
	</tr>
<?
$Row = 'b';
foreach ($FailedLogins as $Item) {
    list($ID, $IP, $UserID, $LastAttempt, $Attempts, $BannedUntil, $Bans, $Username, $PermissionID, $Enabled, $Donor, $Warned) = $Item;
//while(list($ID, $IP, $UserID, $LastAttempt, $Attempts, $BannedUntil, $Bans, $Username, $PermissionID, $Enabled, $Donor, $Warned) = $DB->next_record() ) {
	$Row = ($Row === 'a' ? 'b' : 'a');
    
    
?>
	<tr class="row<?=$Row?>">
			<td>
				<?=$IP?>
			</td>
			<td>
				<? if ($UserID != 0) { echo format_username($UserID, $Username, $Donor, $Warned, $Enabled, $PermissionID); } ?>
			</td>
			<td>
				<?=$Attempts?>
			</td>
			<td>
				<?=time_diff($LastAttempt)?>
			</td>
			<td>
				<?=$Bans?>
			</td>
			<td>
				<?=time_diff($BannedUntil)?>
			</td>	
			<td>
				<form action="" method="post" style="display:inline-block">
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="id" value="<?=$ID?>" />
					<input type="hidden" name="action" value="login_watch" />
					<input type="submit" name="submit" title="remove any bans (and reset attempts) from login watch" value="Unban" />
				</form> 
<? if(check_perms('admin_manage_ipbans')) { ?> 
				<form action="" method="post" style="display:inline-block">
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="id" value="<?=$ID?>" />
					<input type="hidden" name="action" value="ip_ban" />
					<input type="hidden" name="start" value="<?=$IP?>" />
					<input type="hidden" name="end" value="<?=$IP?>" />
					<input type="hidden" name="notes" value="Banned per <?=$Bans?> bans on login watch." />
					<input type="submit" name="submit" title="IP Ban this ip address (use carefully!)" value="IP Ban" />
				</form>
<? } ?>
			</td>
	</tr>
<?
}
?>
</table>
	<div class="linkbox"><?=$Pages?></div>
     
</div>
<? show_footer(); ?>
