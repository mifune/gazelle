<?php
if(!check_perms('site_torrents_notify')) { error(403); }

include(SERVER_ROOT . '/sections/bookmarks/functions.php');

$Bookmarks = all_bookmarks('torrent');

define('NOTIFICATIONS_PER_PAGE', 50);
list($Page,$Limit) = page_limit(NOTIFICATIONS_PER_PAGE);

$TokenTorrents = $Cache->get_value('users_tokens_'.$UserID);
if (empty($TokenTorrents)) {
	$DB->query("SELECT TorrentID, FreeLeech, DoubleSeed FROM users_slots WHERE UserID=$UserID");
	$TokenTorrents = $DB->to_array('TorrentID');
	$Cache->cache_value('users_tokens_'.$UserID, $TokenTorrents);
}

$Results = $DB->query("SELECT SQL_CALC_FOUND_ROWS
		t.ID,
		g.ID,
		g.Name,
		g.NewCategoryID,
		g.TagList,
		t.Size,
		t.FileCount,
		t.Snatched,
		t.Seeders,
		t.Leechers,
		t.Time,
		t.FreeTorrent,
                t.double_seed,
		tln.TorrentID AS LogInDB,
		unt.UnRead,
		unt.FilterID,
		unf.Label
		FROM users_notify_torrents AS unt
		JOIN torrents AS t ON t.ID=unt.TorrentID
		JOIN torrents_group AS g ON g.ID = t.GroupID
		LEFT JOIN users_notify_filters AS unf ON unf.ID=unt.FilterID
		LEFT JOIN torrents_logs_new AS tln ON tln.TorrentID=t.ID
		WHERE unt.UserID='$LoggedUser[ID]'
		GROUP BY t.ID
		ORDER BY t.ID DESC LIMIT $Limit");
$DB->query('SELECT FOUND_ROWS()');
list($TorrentCount) = $DB->next_record();

//Clear before header but after query so as to not have the alert bar on this page load
$DB->query("UPDATE users_notify_torrents SET UnRead='0' WHERE UserID=".$LoggedUser['ID']);
$Cache->delete_value('notifications_new_'.$LoggedUser['ID']);
show_header('My notifications','notifications');

$DB->set_query_id($Results);

$Pages=get_pages($Page,$TorrentCount,NOTIFICATIONS_PER_PAGE,9);
?>
<div class="thin">
    <h2>Notifications</h2>
    <div  class="linkbox">
            [<a href="user.php?action=notify" title="Add or edit notification filters">Add/Edit my notification filters</a>]
    </div>
    <div class="linkbox">
          <?=$Pages?>
    </div>
    <div class="head">Latest notifications <a href="torrents.php?action=notify_clear&amp;auth=<?=$LoggedUser['AuthKey']?>">(clear all)</a> <a href="javascript:SuperGroupClear()">(clear all selected)</a> </div>
<?php
    $NumNotices = $DB->record_count();
    if($NumNotices==0) { ?>
    <div class="box pad center">
           <strong>   No new notifications!  </strong>
    </div>
<?php  } else {
	$FilterGroups = array();
	while($Result = $DB->next_record()) {
		if(!$Result['FilterID']) {
			$Result['FilterID'] = 0;
		}
		if(!isset($FilterGroups[$Result['FilterID']])) {
			$FilterGroups[$Result['FilterID']] = array();
			$FilterGroups[$Result['FilterID']]['FilterLabel'] = ($Result['FilterID'] && !empty($Result['Label']) ? $Result['Label'] : 'unknown filter'.($Result['FilterID']?' ['.$Result['FilterID'].']':''));
		}
		array_push($FilterGroups[$Result['FilterID']], $Result);
	}
	unset($Result);
?>
    <div class="box pad center">
           <strong> <?=$NumNotices?> notifications in <?=count($FilterGroups)?> filters </strong>
    </div>
<?php
	foreach($FilterGroups as $ID => $FilterResults) {
?>
    <br/>
    <div class="head">
        Matches for filter <?=$FilterResults['FilterLabel']?> (<a href="torrents.php?action=notify_cleargroup&amp;filterid=<?=$ID?>&amp;auth=<?=$LoggedUser['AuthKey']?>">Clear</a>) <a href="javascript:GroupClear($('#notificationform_<?=$ID?>').raw())">(clear selected)</a></h3>
    </div>
    <table class="torrent_table">
    <form id="notificationform_<?=$ID?>">
          <tr class="colhead">
                <td style="text-align: center"><input type="checkbox" name="toggle" onClick="ToggleBoxes(this.form, this.checked)" /></td>
                <td class="small cats_col"></td>
                <td style="width:100%;">Torrent</td>
                <td>Files</td>
                <td>Time</td>
                <td>Size</td>
                <td class="sign"><img src="static/styles/<?=$LoggedUser['StyleName']?>/images/snatched.png" alt="Snatches" title="Snatches" /></td>
                <td class="sign"><img src="static/styles/<?=$LoggedUser['StyleName']?>/images/seeders.png" alt="Seeders" title="Seeders" /></td>
                <td class="sign"><img src="static/styles/<?=$LoggedUser['StyleName']?>/images/leechers.png" alt="Leechers" title="Leechers" /></td>
          </tr>
<?php
		unset($FilterResults['FilterLabel']);
		foreach($FilterResults as $Result) {
			list($TorrentID, $GroupID, $GroupName, $GroupCategoryID, $TorrentTags, $Size, $FileCount,
				$Snatched, $Seeders,
				$Leechers, $NotificationTime, $FreeTorrent, $DoubleSeed, $LogInDB, $UnRead, $FilterLabel, $FilterLabel) = $Result;

            $Review = get_last_review($GroupID);

            $DisplayName = '<a href="torrents.php?id='.$GroupID.'" title="View Torrent">'.$GroupName.'</a>'; // &amp;torrentid='.$TorrentID.'

			$TagLinks=array();
			if($TorrentTags!='') {
				$TorrentTags=explode(' ',$TorrentTags);
				foreach ($TorrentTags as $TagKey => $TagName) {
					$TagName = str_replace('_','.',$TagName);
					$TagLinks[]='<a href="torrents.php?taglist='.$TagName.'">'.$TagName.'</a>';
				}
				$TagLinks = implode(', ', $TagLinks);
				$TorrentTags='<br /><div class="tags">'.$TagLinks.'</div>';
			}

            $Icons = torrent_icons(array('FreeTorrent'=>$FreeTorrent,'double_seed'=>$DoubleSeed), $TorrentID, $Review, $Bookmarks);

            $IsMarkedForDeletion = $Review['Status'] == 'Warned' || $Review['Status'] == 'Pending';
		// print row
          $row = $row == 'a' ? 'b' : 'a';
?>
          <tr class="torrent <?=($IsMarkedForDeletion?'redbar':"row$row")?>" id="torrent<?=$TorrentID?>">
                <td style="text-align: center"><input type="checkbox" value="<?=$TorrentID?>" id="clear_<?=$TorrentID?>" /></td>
                <td class="center cats_cols">
                <div title="<?=$NewCategories[$GroupCategoryID]['tag']?>"><img src="<?='static/common/caticons/'.$NewCategories[$GroupCategoryID]['image']?>" /></div>
                </td>
                <td>
                    <?=$Icons?>
                    <?=$DisplayName?>
                    <?php  if($UnRead) { echo '<strong>New!</strong>'; } ?>

                <?php  if ($LoggedUser['HideTagsInLists'] !== 1) { ?>
                      <?=$TorrentTags?>
                <?php  } ?>
                </td>
                <td class="center"><?=number_format($FileCount)?></td>
                <td class="nobr"><?=time_diff($NotificationTime, 1)?></td>
                <td class="nobr"><?=get_size($Size)?></td>
                <td><?=number_format($Snatched)?></td>
                <td<?=($Seeders==0)?' class="r00"':''?>><?=number_format($Seeders)?></td>
                <td><?=number_format($Leechers)?></td>
          </tr>
<?php
		}
?>
    </form>
    </table>
<?php
	}
}

?>
    <div class="linkbox">
          <?=$Pages?>
    </div>
</div>

<?php
show_footer();
