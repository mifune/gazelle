<?

include(SERVER_ROOT . '/sections/tools/managers/speed_functions.php');

 
if(!check_perms('users_manage_cheats')) { error(403); }

$Action = 'speed_cheats';

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
                                    


                                                                    //  'patternupspeed','patternuploaded'
if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('Username', 'upspeed', 'uploaded', 'count', 'time', ))) {
    $_GET['order_by'] = 'upspeed';
    $OrderBy = 'upspeed';   // 'MAX(xbt.upspeed)'; 
} else {
    $OrderBy = $_GET['order_by'];
}

        
$DB->query("SELECT DeleteRecordsMins, KeepSpeed FROM site_options ");
list($DeleteRecordsMins, $KeepSpeed) = $DB->next_record();
        
$ViewSpeed = isset($_GET['viewspeed'])?(int)$_GET['viewspeed']:$KeepSpeed;
$BanSpeed = isset($_GET['banspeed'])?(int)$_GET['banspeed']:$KeepSpeed;

$WHERE = '';
$ViewInfo = ">= ".get_size($ViewSpeed);

if (isset($_GET['viewbanned']) && $_GET['viewbanned']){
    $ViewInfo .= ' (all)';
} else {
    $WHERE .= " AND um.Enabled='1' ";
    //$WHERE .= " AND (um.ID IS NULL OR um.Enabled='1')";
    $ViewInfo .= ' (enabled only)';
}


show_header('Speed Cheats','watchlist');

?>
<div class="thin">
    <h2>(possible) cheaters</h2>
     
	<div class="linkbox">
		<a href="tools.php?action=speed_watchlist">[Watch-list]</a>
		<a href="tools.php?action=speed_excludelist">[Exclude-list]</a>
		<a href="tools.php?action=speed_records">[Speed Records]</a>
		<a href="tools.php?action=speed_cheats">[Speed Cheats]</a>
		<a href="tools.php?action=speed_zerocheats">[Zero Cheats]</a>
	</div>
<?
 
    $CanManage = check_perms('admin_manage_cheats');
?>
    <div class="head">options</div>
    <table class="box pad">
        <form action="tools.php" method="post">
            
                <input type="hidden" name="action" value="save_records_options" />
                <input type="hidden" name="userid" value="<?=$_GET['userid']?>" />
                <input type="hidden" name="torrentid" value="<?=$_GET['torrentid']?>" />
                <input type="hidden" name="viewspeed" value="<?=$ViewSpeed?>" />
                <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
 
            <tr class="colhead"><td colspan="3">view settings: </td></tr>
            <tr class="rowb">
                <td class="center">
                    Viewing: <?=$ViewInfo?> &nbsp; (order: <?="$OrderBy $OrderWay"?>)
                </td>
                <td class="center">
                            <label for="viewbanned" title="Keep Speed">include disabled users </label>
                        <input type="checkbox" value="1" onchange="change_view()"
                               id="viewbanned" name="viewbanned" <? if (isset($_GET['viewbanned']) && $_GET['viewbanned'])echo' checked="checked"'?> />
                </td>
                <td class="center">
                    <label for="viewspeed" title="View Speed">View records with upload speed over </label>
                    <select id="viewspeed" name="viewspeed" title="Hide records under this speed" onchange="change_view()">
                        <option value="0"<?=($ViewSpeed==0?' selected="selected"':'');?>>&nbsp;0&nbsp;&nbsp;</option>
                        <option value="262144"<?=($ViewSpeed==262144?' selected="selected"':'');?>>&nbsp;<?=get_size(262144);?>/s&nbsp;&nbsp;</option>
                        <option value="524288"<?=($ViewSpeed==524288?' selected="selected"':'');?>>&nbsp;<?=get_size(524288);?>/s&nbsp;&nbsp;</option>
                        <option value="1048576"<?=($ViewSpeed==1048576?' selected="selected"':'');?>>&nbsp;<?=get_size(1048576);?>/s&nbsp;&nbsp;</option>
<?                      for($i=2;$i<=20;$i+=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=30;$i<=200;$i+=10){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        /*
                        for($i=18;$i<=32;$i+=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=64;$i<=128;$i+=32){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=256;$i<=1024;$i+=128){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=110;$i<=200;$i+=10){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }
                        for($i=250;$i<=1000;$i+=50){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        } 
                        for($i=1;$i<=4096;$i*=2){ 
                            print_speed_option($i * 1048576 , $ViewSpeed );
                        }*/
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="rowb">
                <td class="center">
                    Pattern Matching:
                </td>
                <td colspan="2" class="left">
                    <label for="viewptnupspeed" title="Display records with matching upspeeds">matching upspeeds</label>
                    <input type="checkbox" value="1" onchange="change_view()"
                         id="viewptnupspeed" name="viewptnupspeed" <? 
                         if (isset($_GET['viewptnupspeed']) && $_GET['viewptnupspeed'])echo' checked="checked"'?> />
                    &nbsp;&nbsp;
                    <label for="viewptnupload" title="Display records with matching upspeeds">matching uploads</label>
                    <input type="checkbox" value="1" onchange="change_view()"
                         id="viewptnupload" name="viewptnupload" <? 
                         if (isset($_GET['viewptnupload']) && $_GET['viewptnupload'])echo' checked="checked"'?> />
                    &nbsp;&nbsp;
                    <label for="viewptnall" title="Display records with matching upspeeds and matching uploaded">toggle both</label>
                    <input type="checkbox" value="1" onchange="toggle_pattern()"
                         id="viewptnall" name="viewptnall" <? 
                         if (isset($_GET['viewptnupspeed']) && $_GET['viewptnupspeed'] && 
                                 isset($_GET['viewptnupload']) && $_GET['viewptnupload'])echo' checked="checked"'?> />
                    
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    (<label for="viewptnzero" title="Show matches with zero speed or zero download stats">show 0 speed/dld matches</label>
                    <input type="checkbox" value="1" onchange="change_view()"
                         id="viewptnzero" name="viewptnzero" <? 
                         if (isset($_GET['viewptnzero']) && $_GET['viewptnzero'] )echo' checked="checked"'?> />)
                </td>
            </tr>
        </form>
        <tr class="colhead"><td colspan="3">group speed ban tool: </td></tr>
        <tr class="rowb">
            <form id="speedrecords" action="tools.php" method="post" onsubmit="return prompt_before_multiban();">
                <input type="hidden" name="action" value="ban_speed_cheat" />
                <input type="hidden" name="returnto" value="cheats" />
                <td class="center"> 
                    <label for="banspeed" title="Ban Speed">Ban users with upload speed over </label>
                    <select id="banspeed" name="banspeed" title="Ban users who have recorded speeds over this"  onchange="preview_users()">
    <?                      for($i=4;$i<=20;$i+=2){ 
                                print_speed_option($i * 1048576 , $BanSpeed );
                            }
                            for($i=30;$i<=200;$i+=10){ 
                                print_speed_option($i * 1048576 , $BanSpeed );
                            }
                            ?>
                    </select>
                    &nbsp;<a href="#" onclick="preview_users();return false;" title="Preview all the users who have recorded a max speed over this">Preview users</a>
                </td>
                <td colspan="2" class="center">  
                    <input type="submit" name="banusers" value="Ban specified users" title="Will ban users over the speed specified" /> 
                </td>
            </form>
        </tr>
    </table>
    <br/>
<?
//---------- print records
            
list($Page,$Limit) = page_limit(25);
 /*
if ($OrderBy=="upspeed") $SQLOrderBy="MAX(xbt.upspeed)";
elseif ($OrderBy=="uploaded") $SQLOrderBy="MAX(xbt.uploaded)"; 
elseif ($OrderBy=="mtime") $SQLOrderBy="MAX(xbt.mtime)"; 
elseif ($OrderBy=="count") $SQLOrderBy="Count(xbt.id)";
else $SQLOrderBy=$OrderBy; 

if (isset($_GET['viewbanned']) && $_GET['viewbanned']){
    $ViewInfo .= ' (all)';
} else {
    $WHERE .= " AND um.Enabled='1' ";
    //$WHERE .= " AND (um.ID IS NULL OR um.Enabled='1')";
    $ViewInfo .= ' (enabled only)';
} */

$GroupBy = '';
$Having = '';
if (isset($_GET['viewptnupspeed']) && $_GET['viewptnupspeed']){
    $GroupBy .= ", xbt.upspeed";
    $Having = 'HAVING Count(xbt.id)>1';
    if (!$_GET['viewptnzero']) $WHERE .= " AND xbt.upspeed!=0 ";
}
if (isset($_GET['viewptnupload']) && $_GET['viewptnupload']){
    $GroupBy .= ", xbt.uploaded";
    $Having = 'HAVING Count(xbt.id)>1';
    if (!$_GET['viewptnzero']) $WHERE .= " AND xbt.uploaded!=0 ";
}


$DB->query("SELECT SQL_CALC_FOUND_ROWS
                             uid, Username, Count(xbt.id) as count, MAX(upspeed) as upspeed, MAX(xbt.uploaded) as uploaded, MAX(mtime) as time, 
                             GROUP_CONCAT(DISTINCT LEFT(xbt.peer_id,8) SEPARATOR '|'), 
                             GROUP_CONCAT(DISTINCT xbt.ip SEPARATOR '|'),
                             ui.Donor, ui.Warned, um.Enabled, um.PermissionID, IF(w.UserID,'1','0')
                          FROM xbt_peers_history AS xbt
                          JOIN users_main AS um ON um.ID=xbt.uid
                          JOIN users_info AS ui ON ui.UserID=xbt.uid
                     LEFT JOIN users_not_cheats AS nc ON nc.UserID=xbt.uid
                     LEFT JOIN users_watch_list AS w ON w.UserID=xbt.uid
                         WHERE (xbt.upspeed)>='$ViewSpeed'
                           AND nc.UserID IS NULL $WHERE
                      GROUP BY xbt.uid $GroupBy 
                        $Having
                      ORDER BY $OrderBy $OrderWay 
                         LIMIT $Limit");


/*  pattern matching: 
SELECT uid, upspeed, uploaded, count(id) as cnt 
FROM xbt_peers_history
JOIN users_main AS um ON um.ID=xbt.uid
JOIN users_info AS ui ON ui.UserID=xbt.uid
WHERE upspeed!=0 
GROUP BY  upspeed, uid, uploaded HAVING cnt > 1
 */


$Records = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 
$Pages=get_pages($Page,$NumResults,25,9);

?>
    
	<div class="linkbox"><?=$Pages?></div>
     
    <div class="head"><?=$NumResults?> users with speed over <?=get_size($ViewSpeed).'/s'?></div>
        <table>
            <tr class="colhead">
                <td style="width:70px"></td>
                <td class="center"><a href="<?=header_link('Username') ?>">User</a></td>
                <td class="center"><a href="<?=header_link('upspeed') ?>">Max UpSpeed</a></td>
                <td class="center"><a href="<?=header_link('uploaded') ?>">Max Uploaded</a></td>
                <td class="center" title="number of records that are over the speed limit"><a href="<?=header_link('count') ?>">count</a></td>
                <td class="center"><span style="color:#777">-clientID-</span></td>
                <td class="center">Client IP addresses</td>
                <td class="center" style="min-width:120px"><a href="<?=header_link('time') ?>">last seen</a></td>
            </tr>
<?
            $row = 'a';
            if($NumResults==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="8">no speed records</td>
                    </tr>
<?
            } else {
                foreach ($Records as $Record) {
                    list( $UserID, $Username, $CountRecords, $MaxUpSpeed, $MaxUploaded, $LastTime, $PeerIDs, $IPs, 
                            $IsDonor, $Warned, $Enabled, $ClassID, $OnWatchlist) = $Record;
                    $row = ($row === 'a' ? 'b' : 'a');
                    
                    $PeerIDs = explode('|', $PeerIDs);
                    $IPs = explode('|', $IPs);
 
                    /*
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
                LIMIT 20"); */
                    
                    $DB->query("SELECT uh.UserID AS UserID, uh.IP 
                                  FROM users_history_ips AS uh 
                                  JOIN users_history_ips AS me ON uh.IP=me.IP 
                                 WHERE uh.IP != '127.0.0.1' AND uh.IP !='' AND me.UserID = $UserID AND uh.UserID != $UserID 
                              GROUP BY UserID, IP
                              ORDER BY UserID, IP  
                                 LIMIT 50"); 
    
                    $IPDupeCount = $DB->record_count();
                    $IPDupes = $DB->to_array();
                    
                    $bantype=1; //'ban_speed_cheat'; 
                    $viewlink='';
                    $pattern=0;
                    if ($_GET['viewptnupspeed']==1){
                        $viewlink="&matchspeed=$MaxUpSpeed";
                        $bantype=2; //'ban_pattern_cheat';
                        $pattern=$CountRecords;
                    }
                    if ($_GET['viewptnupload']==1) {
                        $viewlink.="&matchuploaded=$MaxUploaded";
                        $bantype=2; //'ban_pattern_cheat';
                        $pattern=$CountRecords;
                    }
                    if($pattern>0) $pattern= "&pattern=$pattern";
                    else $pattern='';
                    
?> 
                    <tr class="row<?=$row?>">
                        <td>
                           <a href="?action=speed_records&viewspeed=0<?=$viewlink?>&userid=<?=$UserID?>" title="View records for just <?=$Username?>"><img src="static/common/symbols/view.png" alt="view" /></a> 
                           <div style="display:inline-block">
<?                         if (!$OnWatchlist) {         // array_key_exists($UserID, $Watchlist)) {   
?>                            <a onclick="watchlist_add('<?=$UserID?>',true);return false;" href="#" title="Add <?=$Username?> to watchlist"><img src="static/common/symbols/watchedred.png" alt="wl add" /></a><br/><?
                           }   
                            //if (!array_key_exists($UserID, $Excludelist)) {   
 ?>                           <a onclick="excludelist_add('<?=$UserID?>',true);return false;" href="#" title="Add <?=$Username?> to exclude list"><img src="static/common/symbols/watchedgreen.png" alt="excl add" /></a><?
                            //}  
 ?>                        </div>
                           <a onclick="remove_records('<?=$UserID?>');return false;" href="#" title="Remove all speed records belonging to <?=$Username?> from stored records"><img src="static/common/symbols/trash.png" alt="del records" /></a>
<?
                            if ($Enabled=='1'){  
                                if($bantype==1) { ?>
                                <a href="tools.php?action=ban_speed_cheat<?=$pattern?>&banuser=1&returnto=cheats&userid=<?=$UserID?>" title="ban this user for being a big fat cheat (speeding)"><img src="static/common/symbols/ban.png" alt="ban" /></a>
<?                              } else {?>
                                <a href="tools.php?action=ban_pattern_cheat<?=$pattern?>&banuser=1&returnto=cheats&userid=<?=$UserID?>" title="ban this user for being a big fat cheat (pattern matching)"><img src="static/common/symbols/ban2.png" alt="ban" /></a>
<?                              }
                            }
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
                        <td class="center"><?=get_size($MaxUploaded)?></td>
                        <td class="center"><?=$CountRecords?></td>
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
                list($EUserID, $IP) = $IPDupe;  // , $EType1, $EType2
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
                    <?="$Username's history <-> $DupeInfo[Username]'s history"?>
                    <?//="$Username's $EType1 <-> $DupeInfo[Username]'s $EType2"?>
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