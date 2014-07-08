<?php
$GroupID = $_GET['groupid'];
$TorrentID = $_GET['torrentid'];

if (!is_number($GroupID) || !is_number($TorrentID)) { error(0); }

$DB->query("SELECT LastReseedRequest, UserID, Time FROM torrents WHERE ID='$TorrentID'");
list($LastReseedRequest, $UploaderID, $UploadedTime) = $DB->next_record();

if (time()-strtotime($LastReseedRequest)<259200 && !check_perms('site_debug')) { error("There was already a re-seed request for this torrent within the past 3 days."); }

$DB->query("UPDATE torrents SET LastReseedRequest='" .sqltime(). "' WHERE ID='$TorrentID'");

$Cache->delete_value('torrents_details_'.$GroupID);

$Group = get_groups(array($GroupID));
$Group = array_pop($Group['matches']);
list($GroupID, $GroupName, $TagList, $Torrents) = array_values($Group);

$Name = $GroupName;

$DB->query("SELECT uid, Max(tstamp) AS tstamp FROM xbt_snatched WHERE fid='$TorrentID' GROUP BY uid ORDER BY tstamp DESC LIMIT 40");
if ($DB->record_count()>0) {
    $Users = $DB->to_array('uid');
    foreach ($Users as $User) {
        list($UserID, $TimeStamp) = $User;
        $Request = "Hi [you],

The user [url=http://".SITE_URL."/user.php?id=$LoggedUser[ID]]$LoggedUser[Username][/url] has requested a re-seed for the torrent [url=http://".SITE_URL."/torrents.php?id=$GroupID&torrentid=$TorrentID]".$Name."[/url], which you snatched on ".date('M d Y', $TimeStamp).". The torrent is now un-seeded, and we need your help to resurrect it!

The exact process for re-seeding a torrent is slightly different for each client, but the concept is the same. The idea is to download the .torrent file and open it in your client, and point your client to the location where the data files are, then initiate a hash check.

Thanks!";

        send_pm($UserID, 0, 'Re-seed request for torrent '.db_string($Name), db_string($Request));
    }
    $NumUsers = count($Users);
}

if ( !isset($Users[$UploaderID])) {
    $UserInfo = user_info($UploaderID);
    $Username = $UserInfo['Username'];

    $Request = "Hi $Username,

The user [url=http://".SITE_URL."/user.php?id=$LoggedUser[ID]]$LoggedUser[Username][/url] has requested a re-seed for the torrent [url=http://".SITE_URL."/torrents.php?id=$GroupID&torrentid=$TorrentID]".$Name."[/url], which you uploaded on ".date('M d Y', strtotime($UploadedTime)).". The torrent is now un-seeded, and we need your help to resurrect it!

The exact process for re-seeding a torrent is slightly different for each client, but the concept is the same. The idea is to download the .torrent file and open it in your client, and point your client to the location where the data files are, then initiate a hash check.

Thanks!";
    send_pm($UploaderID, 0, 'Re-seed request for torrent '.db_string($Name), db_string($Request));

    $NumUsers += 1;
}

show_header();
?>
<div class="thin">
    <h2>Successfully sent re-seed request</h2>
    <div class="head"></div>
    <div class="box pad center">
        Successfully sent re-seed request for torrent <a href="torrents.php?id=<?=$GroupID?>&amp;torrentid=<?=$TorrentID?>"><?=display_str($Name)?></a> to <?=$NumUsers?> user(s).
    </div>
</div>
<?php
show_footer();
