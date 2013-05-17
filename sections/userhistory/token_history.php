<?
/************************************************************************
||------------------|| User token history page ||-----------------------||
This page lists the torrents a user has spent his tokens on. It
gets called if $_GET['action'] == 'token_history'.

Using $_GET['userid'] allows a mod to see any user's token history.
Nonmods and empty userid show $LoggedUser['ID']'s history
************************************************************************/

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
    return "userhistory.php?action=token_history&amp;order_way=$NewWay&amp;order_by=$SortKey&amp;" . get_url(array('action', 'order_way', 'order_by'));
}

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}


                                    // 'upspeed', 
if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('Torrent', 'Size', 'Time', 'Freeleech' ,'Doubleseed' ))) {
    $_GET['order_by'] = 'Time';
    $OrderBy = 'Time'; 
} else {
    $OrderBy = $_GET['order_by'];
}




if (isset($_GET['userid'])) {
	$UserID = $_GET['userid'];
} else {
	$UserID = $LoggedUser['ID'];
}
if (!is_number($UserID)) { error(404); }

$UserInfo = user_info($UserID);
$Perms = get_permissions($UserInfo['PermissionID']);
$UserClass = $Perms['Class'];

if($LoggedUser['ID'] != $UserID && !check_paranoia(false, $User['Paranoia'], $UserClass, $UserID)) {
	error(PARANOIA_MSG);
}

if (isset($_GET['expire'])) {
	if (!check_perms('users_mod')) { error(403); }
	$UserID = $_GET['userid'];
	$TorrentID = $_GET['torrentid'];
	
	if (!is_number($UserID) || !is_number($TorrentID)) { error(403); }
	$DB->query("SELECT info_hash FROM torrents where ID = $TorrentID");
	if (list($InfoHash) = $DB->next_record(MYSQLI_NUM, FALSE)) {
		$DB->query("DELETE FROM users_slots WHERE UserID=$UserID AND TorrentID=$TorrentID");
		$Cache->delete_value('users_tokens_'.$UserID);
		update_tracker('remove_tokens', array('info_hash' => rawurlencode($InfoHash), 'userid' => $UserID));
	}
	header("Location: userhistory.php?action=token_history&userid=$UserID");
}

show_header('Current slots in use');

list($Page,$Limit) = page_limit(50);

$DB->query("SELECT SQL_CALC_FOUND_ROWS
			   us.TorrentID,
			   t.GroupID,
               t.Size,
               tg.Time,
			   us.FreeLeech,
               us.DoubleSeed,
			   tg.Name as Torrent
			FROM users_slots AS us
			JOIN torrents AS t ON t.ID = us.TorrentID
			JOIN torrents_group AS tg ON tg.ID = t.GroupID
		   WHERE us.UserID = $UserID
		ORDER BY $OrderBy $OrderWay 
           LIMIT $Limit");
$Tokens = $DB->to_array();

$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
$Pages=get_pages($Page, $NumResults, 50);

?>
<div class="thin">
    <div class="linkbox"><?=$Pages?></div>
    <div class="head">Slots in use for <?=format_username($UserID, $UserInfo['Username'], $UserInfo['Donor'], $UserInfo['Warned'], $UserInfo['Enabled'])?></div>
    <table>
        <tr class="colhead">
            <td style="width:45%"><a href="<?=header_link('Torrent') ?>">Torrent</a></td>
            <td><a href="<?=header_link('Size') ?>">Size</a></td>
            <td><a href="<?=header_link('Time') ?>">Time posted</a></td>
            <td class="center"><a href="<?=header_link('Freeleech') ?>">Freeleech</a></td>
            <td class="center"><a href="<?=header_link('Doubleseed') ?>">Doubleseed</a></td>
        </tr>
<?
    foreach ($Tokens as $Token) {
        $GroupIDs[] = $Token['GroupID'];
    }

    $i = true;
    foreach ($Tokens as $Token) {
        $i = !$i;
        list($TorrentID, $GroupID, $Size, $Time, $FreeLeech, $DoubleSeed, $Name) = $Token; 
        $Name = "<a href=\"torrents.php?torrentid=$TorrentID\">$Name</a>";
            if ($FreeLeech == '0000-00-00 00:00:00') {
                $fl = 'No';
            } else {
                $fl = time_diff($FreeLeech,2,true,false,1) ;
                //if ($FreeLeech <= sqltime() ) $fl = '<span style="color:red">'.$fl. '</span>' ;
                
                $fl = $FreeLeech > sqltime() ? 
                    time_diff($FreeLeech) : '<span style="color:red" title="' . time_diff($FreeLeech,2,false,false,1) . '">Expired</span>';
            }

            if ($DoubleSeed == '0000-00-00 00:00:00') {
                $ds = 'No';
            } else {
                $ds = time_diff($DoubleSeed,2,true,false,1);
                //if ($DoubleSeed <= sqltime() ) $ds = '<span style="color:red">'.$ds. '</span>' ;
                
                $ds = $DoubleSeed > sqltime() ?
                    time_diff($DoubleSeed) : '<span style="color:red" title="' . time_diff($DoubleSeed,2,false,false,1) . '">Expired</span>';
            }
?>
        <tr class="<?=($i?'rowa':'rowb')?>">
            <td><?=$Name?></td>
            <td><?=get_size($Size)?></td>
            <td><?=time_diff($Time,2,true,false,1)?></td>
            <td class="center"><?=$fl?></td>
            <td class="center"><?=$ds?></td>
        </tr>
<?  }       ?>
    </table>
    <div class="linkbox"><?=$Pages?></div>
</div>

<?
show_footer();
?>