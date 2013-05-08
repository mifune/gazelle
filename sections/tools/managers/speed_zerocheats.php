<?

include(SERVER_ROOT . '/sections/tools/managers/speed_functions.php');
 

if(!check_perms('users_manage_cheats')) { error(403); }

$Action = 'speed_zerocheats';

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}

                       //     xbt.id, uid, Username, xbt.downloaded, remaining, t.Size, xbt.uploaded, 
                     ////       upspeed, downspeed, timespent, peer_id, xbt.ip, tg.ID, fid, tg.Name, xbt.mtime
                                    
// User 	Remaining 	Uploaded 	UpSpeed 	ClientIPaddress 	date time 	
//	TorrentID 	Total 	Downloaded 	DownSpeed 	                	total time 
                                    
if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('Username', 'upspeed', 'peercount', 'grabbed', 'time' ))) {
    $_GET['order_by'] = 'upspeed';
    $OrderBy = 'upspeed'; 
} else {
    $OrderBy = $_GET['order_by'];
}

         
show_header('Zero Stat Cheats','watchlist');

?>
<div class="thin">
    <h2>(possible) zero stat cheaters</h2>
  
	<div class="linkbox">
		<a href="tools.php?action=speed_watchlist">[Watch-list]</a>
		<a href="tools.php?action=speed_excludelist">[Exclude-list]</a>
		<a href="tools.php?action=speed_records">[Speed Records]</a>
		<a href="tools.php?action=speed_cheats">[Speed Cheats]</a>
		<a href="tools.php?action=speed_zerocheats">[Zero Cheats]</a>
	</div> 
<?

    $CanManage = check_perms('admin_manage_cheats');
 
//---------- print records
            
list($Page,$Limit) = page_limit(50);
  
 
 

/*
 * 
 * Matching for 0 stat cheats
   
   SELECT SQL_CALC_FOUND_ROWS
                   uid, Username, COUNT(x.fid), Count(DISTINCT ud.TorrentID) as Grabbed, MAX(x.upspeed), MAX(x.mtime),
                             GROUP_CONCAT(DISTINCT LEFT(x.peer_id,8) SEPARATOR '|'), 
                             GROUP_CONCAT(DISTINCT x.ip SEPARATOR '|'),
                             ui.Donor, ui.Warned, um.Enabled, um.PermissionID
   FROM xbt_files_users AS x 
   JOIN torrents AS t ON t.ID=x.fid AND x.active=1 
   JOIN users_main AS um ON um.ID=x.uid AND  um.Downloaded=0 AND ( um.Uploaded=524288000 OR  um.Uploaded=0) AND um.Enabled='1'  
   JOIN users_info AS ui ON ui.UserID=um.ID
   LEFT JOIN users_downloads AS ud ON ud.UserID=um.ID
   GROUP BY x.uid LIMIT 50
  
   
   SELECT SQL_CALC_FOUND_ROWS
                   uid, Username, COUNT(x.fid), Count(DISTINCT ud.TorrentID) as Grabbed, MAX(x.upspeed), MAX(x.mtime)
   FROM xbt_files_users AS x 
   JOIN torrents AS t ON t.ID=x.fid AND x.active=1 
   JOIN users_main AS um ON um.ID=x.uid AND  um.Downloaded=0 AND ( um.Uploaded=524288000 OR  um.Uploaded=0) AND um.Enabled='1'  
   JOIN users_info AS ui ON ui.UserID=um.ID
   LEFT JOIN users_downloads AS ud ON ud.UserID=um.ID
   JOIN torrents AS t2 ON t2.ID=ud.TorrentID 
   GROUP BY x.uid LIMIT 50
  
 * 
 * 
 * 
 * 
 * 
   SELECT SQL_CALC_FOUND_ROWS
                   uid, Username, COUNT(x.fid) as PeerOn, Count(DISTINCT ud.TorrentID) as Grabbed, MAX(x.upspeed), MAX(x.mtime)
   FROM xbt_files_users AS x 
   JOIN torrents AS t ON t.ID=x.fid AND x.active=1 
   JOIN users_main AS um ON um.ID=x.uid AND  um.Downloaded=0 AND ( um.Uploaded=524288000 OR  um.Uploaded=0) AND um.Enabled='1'  
   JOIN users_info AS ui ON ui.UserID=um.ID
   LEFT JOIN users_downloads AS ud ON ud.UserID=um.ID
   GROUP BY x.uid 
   HAVING Grabbed>1
   LIMIT 50
  +--------+-----------------+--------------+---------+----------------+--------------+
| uid    | Username        | PeerOn | Grabbed | MAX(x.upspeed) | MAX(x.mtime) |
+--------+-----------------+--------------+---------+----------------+--------------+
|  49833 | Godzilla666     |    111 |     108 |              0 |   1367527685 |
|  50626 | d576            |    135 |      25 |              0 |   1367535391 |
| 111035 | azxxx           |    204 |     185 |              0 |   1367535232 |
| 114808 | parko           |   8110 |     783 |              0 |   1367530276 |
| 134912 | kate28archer    |     32 |      30 |              0 |   1367535340 |
| 203674 | ham24           |    386 |     186 |              0 |   1367533086 |
| 255877 | vale4606        |   6699 |     201 |              0 |   1367535465 |
| 255915 | HoldFast        |   9190 |    1607 |              0 |   1367534680 |
| 310499 | hammerstein     |           93 |      83 |              0 |   1367534334 |
| 313227 | moni            |            1 |       1 |              0 |   1367534591 |
| 326640 | lisfox          |            3 |       3 |              0 |   1367526366 |
| 328759 | jimbojones21    |            4 |       2 |              0 |   1367534677 |
| 338754 | jeff5789        |            6 |       3 |              0 |   1367530694 |
| 341348 | porcoepoppante2 |            9 |       3 |              0 |   1367534865 |
| 343688 | hedgeknight     |            1 |       1 |              0 |   1367535106 |
| 345836 | blueballs333    |           14 |       4 |              0 |   1367535299 |
| 345841 | ZNemesis        |            4 |       2 |              0 |   1367535395 |
| 346580 | markspencer0728 |            1 |       1 |              0 |   1367533592 |
| 346808 | greenbottle     |           16 |       4 |              0 |   1367532069 |
| 346843 | Whoozit         |            1 |       1 |              0 |   1367534473 |
| 346852 | killyou85       |            1 |       1 |              0 |   1367534295 |
| 346861 | kamrod23        |            1 |       1 |              0 |   1367533750 |
| 346862 | ravana88        |            4 |       2 |              0 |   1367533259 |
| 346864 | filiz1971       |            9 |       3 |              0 |   1367533871 |
| 346873 | rabbit_hook     |            8 |       1 |              0 |   1367535256 |
| 346876 | lokiju          |            8 |       4 |              0 |   1367535413 |
| 346881 | babelbubel      |            2 |       1 |              0 |   1367534670 |
| 346884 | jakaruuu        |           70 |      14 |              0 |   1367535158 |
| 346886 | loloforo        |            4 |       2 |              0 |   1367535420 |
+--------+-----------------+--------------+---------+----------------+--------------+

   +--------+-------------+--------+---------+----------------+--------------+
| uid    | Username    | PeerOn | Grabbed | MAX(x.upspeed) | MAX(x.mtime) |
+--------+-------------+--------+---------+----------------+--------------+
|  49833 | Godzilla666 |    345 |     111 |              0 |   1367617612 |
|  84482 | devid       |    108 |      89 |              0 |   1367617448 |
|  90768 | Copywright  |    531 |     171 |              0 |   1367610788 |
| 114808 | parko       |  18612 |     817 |              0 |   1367618295 |
| 155944 | SoLaTiDo    |    352 |     167 |              0 |   1367611759 |
| 203674 | ham24       |    388 |     187 |              0 |   1367617889 |
| 255877 | vale4606    |   6496 |     201 |              0 |   1367618331 |
| 270385 | slash14     |    220 |      55 |              0 |   1367618326 |
| 310499 | hammerstein |    651 |      83 |              0 |   1367617046 |
| 336352 | allexx      |      6 |       6 |              0 |   1367615142 |
| 339335 | aberel      |     12 |       3 |              0 |   1367618336 |
| 345841 | ZNemesis    |      4 |       2 |              0 |   1367617447 |
| 347372 | ZuluFace    |      2 |       2 |              0 |   1367611151 |
| 347444 | criftler2   |     16 |       4 |              0 |   1367610369 |
| 347507 | soler88     |     12 |       4 |              0 |   1367618196 |
+--------+-------------+--------+---------+----------------+--------------+

 * 
 * 
 */





/*  
 * 
 * 
                         WHERE nc.UserID IS NULL
 */
    
    
$DB->query("SELECT SQL_CALC_FOUND_ROWS
                   uid, Username, COUNT(x.fid) as Peercount, Count(DISTINCT ud.TorrentID) as Grabbed, 
                            MAX(x.upspeed) as upspeed, MAX(x.mtime) as time,
                             GROUP_CONCAT(DISTINCT LEFT(x.peer_id,8) SEPARATOR '|'), 
                             GROUP_CONCAT(DISTINCT x.ip SEPARATOR '|'),
                             ui.Donor, ui.Warned, um.Enabled, um.PermissionID, IF(w.UserID,'1','0')
               FROM xbt_files_users AS x 
               JOIN torrents AS t ON t.ID=x.fid AND x.active=1 
               JOIN users_main AS um ON um.ID=x.uid AND  um.Downloaded=0 AND ( um.Uploaded=524288000 OR  um.Uploaded=0) AND um.Enabled='1'  
               JOIN users_info AS ui ON ui.UserID=um.ID
               LEFT JOIN users_downloads AS ud ON ud.UserID=um.ID
               LEFT JOIN users_watch_list AS w ON w.UserID=x.uid
               LEFT JOIN users_not_cheats AS nc ON nc.UserID=x.uid
                      GROUP BY x.uid   
                      ORDER BY $OrderBy $OrderWay 
                         LIMIT $Limit");

$Records = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 
$Pages=get_pages($Page,$NumResults,50,9);

?>
    
	<div class="linkbox"><?=$Pages?></div>
     
    <div class="head"><?=$NumResults?> users with suspicious zero stats</div>
        <table>
            <tr class="colhead">
                <td style="width:70px"></td>
                <td class="center"><a href="<?=header_link('Username') ?>">User</a></td>
                <td class="center"><a href="<?=header_link('upspeed') ?>">Max UpSpeed</a></td>
                <td class="center" title="number of current peer records"><a href="<?=header_link('peercount') ?>">count</a></td>
                <td class="center" title="number of grabbed files"><a href="<?=header_link('grabbed') ?>">grabbed</a></td>
                <td class="center"><span style="color:#777">-clientID-</span></td>
                <td class="center">Client IP addresses</td>
                <td class="center" style="min-width:120px"><a href="<?=header_link('time') ?>">last seen</a></td>
                <!--<td class="center"></td>-->
            </tr>
<?
            $row = 'a';
            if($NumResults==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="8">no zero stat peers</td>
                    </tr>
<?
            } else {
                foreach ($Records as $Record) {
                    list( $UserID, $Username, $CountRecords, $Grabbed, $MaxUpSpeed, $LastTime, 
                            $PeerIDs, $IPs, $IsDonor, $Warned, $Enabled, $ClassID, $OnWatchlist, $OnExcludelist) = $Record;
                    $row = ($row === 'a' ? 'b' : 'a');
                    
                    $PeerIDs = explode('|', $PeerIDs);
                    $IPs = explode('|', $IPs);
            
                    
	$DB->query(" (SELECT e.UserID AS UserID, um.IP, 'account', 'history' FROM users_main AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID!= $UserID AND um.ID = $UserID)
                UNION
                 (SELECT e.ID AS UserID, um.IP, 'account', 'account' FROM users_main AS um JOIN users_main AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.ID!= $UserID AND um.ID = $UserID)
                UNION
                 (SELECT um.ID AS UserID, um.IP, 'history', 'account' FROM users_main AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID = $UserID AND um.ID != $UserID)
                UNION
                 (SELECT um.UserID AS UserID, um.IP, 'history', 'history' FROM users_history_ips AS um JOIN users_history_ips AS e ON um.IP=e.IP 
				 WHERE um.IP != '127.0.0.1' AND um.IP !='' AND e.UserID = $UserID AND um.UserID != $UserID)
                ORDER BY  UserID, IP 
                LIMIT 20");
                    $IPDupeCount = $DB->record_count();
                    $IPDupes = $DB->to_array();
                    
?> 
                    <tr class="row<?=$row?>">
                        <td>
                           <a href="?action=speed_records&viewspeed=0&userid=<?=$UserID?>" title="View records for just <?=$Username?>"><img src="static/common/symbols/view.png" alt="view" /></a> 
                           <div style="display:inline-block">
<?                         if (!$OnWatchlist) {   
?>                            <a onclick="watchlist_add('<?=$UserID?>',true);return false;" href="#" title="Add <?=$Username?> to watchlist"><img src="static/common/symbols/watchedred.png" alt="wl add" /></a><br/><?
                           }   
                            if (!$OnExcludelist) {   
 ?>                           <a onclick="excludelist_add('<?=$UserID?>',true);return false;" href="#" title="Add <?=$Username?> to exclude list"><img src="static/common/symbols/watchedgreen.png" alt="excl add" /></a><?
                            }  
 ?>                        </div>
                           <a onclick="remove_records('<?=$UserID?>');return false;" href="#" title="Remove all speed records belonging to <?=$Username?> from stored records"><img src="static/common/symbols/trash.png" alt="del records" /></a>
<?
                            if ($Enabled=='1'){  ?>
                                <a href="tools.php?action=ban_zero_cheat&banuser=1&userid=<?=$UserID?>" title="ban this user for being a big fat zero stat cheat"><img src="static/common/symbols/ban2.png" alt="ban" /></a>
<?                          }
                           
                           ?>
                        </td>
                        <td class="center">
<?                          echo format_username($UserID, $Username, $IsDonor, $Warned, $Enabled, $ClassID, false, false);  

                            if ($IPDupeCount>0) { ?> 
                             
                            <span style="float:right;">
                                <a href="#" title="view matching ip's for this user" onclick="$('#linkeddiv<?=$UserID?>').toggle();this.innerHTML=this.innerHTML=='(hide)'?'(view)':'(hide)';return false;">(view)</a>
                            </span>
<?
                            }
?>
                            
                        </td>
                        <td class="center"><?=speed_span($MaxUpSpeed, $KeepSpeed, 'red', get_size($MaxUpSpeed).'/s')?></td>
                        <td class="center"><?=$CountRecords?></td>
                        <td class="center"><?=$Grabbed?></td>
                        <td class="center"><?
                            foreach($PeerIDs as $PeerID) {
                        ?>  <span style="color:#555"><?=substr($PeerID,0,8)  ?></span> <br/>
                        <?  } ?>
                        </td>
                        <td class="center"><?
                            foreach($IPs as $IP) {
                                $ipcc = geoip($IP);
                                echo display_ip($IP, $ipcc)."<br/>";
                            }
                        ?> 
                        </td>
                        <td class="center"><?=time_diff($LastTime, 2, true, false, 1)?></td>
                    </tr>
<?
            if ($IPDupeCount>0) { 
?>
                    <tr id="linkeddiv<?=$UserID?>" style="font-size:0.9em;" class="hidden row<?=$row?>">
                        <td colspan="8"> 
            <table width="100%" class="border">
<?
            $i = 0;
            foreach($IPDupes AS $IPDupe) {
                list($EUserID, $IP, $EType1, $EType2) = $IPDupe;
                $i++;
                $DupeInfo = user_info($EUserID);
?> 
            <tr>

                <td align="center">
                    <?=format_username($EUserID, $DupeInfo['Username'], $DupeInfo['Donor'], $DupeInfo['Warned'], $DupeInfo['Enabled'], $DupeInfo['PermissionID'])?>
                </td>
                <td align="center">
                    <?=display_ip($IP, $DupeInfo['ipcc'])?>
                </td>
                <td align="left">
                    <?="$Username's $EType1 <-> $DupeInfo[Username]'s $EType2"?>
                </td>
                <td>
<?
                    if ( !array_key_exists($EUserID, $Dupes) ) {
?>
						[<a href="user.php?action=dupes&dupeaction=link&auth=<?=$LoggedUser['AuthKey']?>&userid=<?=$UserID?>&targetid=<?=$EUserID?>" title="link this user to <?=$Username?>">link</a>]
<?
                    }
?>
                </td> 
            </tr>
<?
            }
?>
            </table>
                            
                        </td>
                    </tr>
<?
            }
?> 
               
<?
                   

                }
            }
            ?>
        </table>
	<div class="linkbox"><?=$Pages?></div>
</div>
<? show_footer(); ?>