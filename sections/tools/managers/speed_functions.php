    
<?
 
// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = "desc") {
    global $Action, $OrderBy, $OrderWay;
    if ($SortKey == $OrderBy) {
        if ($OrderWay == "desc") {
            $NewWay = "asc";
        } else {
            $NewWay = "desc";
        }
    } else {
        $NewWay = $DefaultWay;
    }

    return "tools.php?action=$Action&amp;order_way=$NewWay&amp;order_by=$SortKey&amp;" . get_url(array('action', 'order_way', 'order_by'));
}

function format_torrentid($torrentID, $name, $maxlen = 20) {
    if ($torrentID == 0) return 'None';
    if ($name == '') $tname = $torrentID;
    else $tname = $name;
    $str = '<a href="torrents.php?torrentid='.$torrentID.'" title="'.$tname.'">'. cut_string($tname, $maxlen).'</a>';
    if ($name == '') $str = "torrent not found [$str]";
    return $str;
}
function size_span($speed, $text) {
    return '<span style="color:'.($speed>0?'black':'lightgrey').'">'.$text.'</span>';
}
function speed_span($speed, $highlightlimit, $color, $text) {
    if ($speed>=$highlightlimit) $scolor = $color;
    elseif ($speed>0) $scolor = 'black';
    else $scolor = 'lightgrey';
    return '<span style="color:'.$scolor.'">'.$text.'</span>';
}


function print_speed_option($speed, $selected_speed){
?>
    <option value="<?=$speed?>" <?=($selected_speed==$speed?' selected="selected"':'');?>>&nbsp;<?=get_size($speed);?>/s&nbsp;&nbsp;</option>
<?
}

/*
function get_user_notcheatslist() {
    global $DB;
    $DB->query("SELECT wl.UserID, um.Username, StaffID, um2.Username AS Staffname, Time, wl.Comment, 
                                 ui.Donor, ui.Warned, um.Enabled, um.PermissionID
                  FROM users_not_cheats AS wl
             LEFT JOIN users_main AS um ON um.ID=wl.UserID
             LEFT JOIN users_info AS ui ON ui.UserID=wl.UserID
             LEFT JOIN users_main AS um2 ON um2.ID=wl.StaffID
              ORDER BY Time DESC");
    
    $Userlist = $DB->to_array('UserID');
    return $Userlist;
}

function print_user_notcheatslist() {
    $Userlist = get_user_notcheatslist();
    print_user_list($Userlist,'excludelist','Exclude users list','watchedgreen', 
            'Users in this list will be excluded from the multiban function and will not be shown on the cheats page');
    return $Userlist;
}
 
function get_user_watchlist() {
    global $DB;
    $DB->query("SELECT wl.UserID, um.Username, StaffID, um2.Username AS Staffname, Time, wl.Comment, 
                                 ui.Donor, ui.Warned, um.Enabled, um.PermissionID
                  FROM users_watch_list AS wl
             LEFT JOIN users_main AS um ON um.ID=wl.UserID
             LEFT JOIN users_info AS ui ON ui.UserID=wl.UserID
             LEFT JOIN users_main AS um2 ON um2.ID=wl.StaffID
              ORDER BY Time DESC");
    
    $Watchlist = $DB->to_array('UserID');
    return $Watchlist;
}


function print_user_watchlist() {
    $Watchlist = get_user_watchlist();
    print_user_list($Watchlist,'watchlist','User watch list','watchedred', 'Users in the watch list will have their records retained until they are manually deleted. You can use this information to help detect ratio cheaters.<br/>
                    note: use the list sparingly - this can quickly fill the database with a huge number of records.');
    return $Watchlist;
} */




function print_user_list($Userlist,$ListType,$Title,$TitleIcon,$Help) {
    
?> 
        <div class="head"><?=$Title?> &nbsp;<img src="static/common/symbols/<?=$TitleIcon?>.png" alt="view" /><span style="float:right;"><a href="#" onclick="$('#<?=$ListType?>').toggle();this.innerHTML=this.innerHTML=='(hide)'?'(view)':'(hide)';">(hide)</a></span>&nbsp;</div>
        <table id="<?=$ListType?>" class="">
            <tr class="rowa"> 
                <td colspan="6" style="text-align: left;color:grey"> 
                    <?=$Help?>
                </td>
            </tr>
            <tr class="colhead">
                <td class="center"></td>
                <td class="center"><a href="<?=header_link('Username') ?>">User</a></td>
                <td class="center"><a href="<?=header_link('Time') ?>">Time added</a></td>
                <td class="center"><a href="<?=header_link('Staffname') ?>">added by</a></td>
                <td class="center"><a href="<?=header_link('Comment') ?>">comment</a></td>
                <!--<td class="center" width="100px" title="keep torrent records related to this user">keep torrents</td>-->
                <td class="center" width="120px"></td>
            </tr>
<?
            $row = 'a';
            if(count($Userlist)==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="7">no users on <?=$Title?></td>
                    </tr>
<?
            } else {
                foreach ($Userlist as $Watched) {
                    list($UserID, $Username, $StaffID, $Staffname, $Time, $Comment,
                                       $IsDonor, $Warned, $Enabled, $ClassID) = $Watched;
                    $row = ($row === 'b' ? 'a' : 'b');
?> 
    
                    <tr class="row<?=$row?>">
                     
                            <td class="center">
                                <a href="?action=speed_records&viewspeed=<?=$ViewSpeed?>&userid=<?=$UserID?>" title="View records for just <?=$Username?>"><img src="static/common/symbols/view.png" alt="view" /></a>
<?                          if ($ListType=='watchlist' && $Enabled=='1'){ ?>
                                &nbsp;<a href="tools.php?action=ban_speed_cheat&banuser=1&userid=<?=$UserID?>" title="ban this user for being a big fat cheat (speeding)"><img src="static/common/symbols/ban.png" alt="ban" /></a>
<?                          }  ?>
                            </td>
                            <td class="center"><?=format_username($UserID, $Username, $IsDonor, $Warned, $Enabled, $ClassID, false, false)?></td>
                            <td class="center"><?=time_diff($Time, 2, true, false, 1)?></td>
                            <td class="center"><?=format_username($StaffID, $Staffname)?></td>
                            <td class="center" title="<?=$Comment?>"><?=cut_string($Comment, 40)?></td>
                            <!--<td class="center">
                                <input type="checkbox" name="keeptorrent" <?if($KeepTorrents)echo'checked="checked"'?> value="1" title="if checked keep all torrent records this user is on as well" />
                            </td>-->
                            <td class="center">
                              <a onclick="remove_records('<?=$UserID?>');return false;" href="#" title="Remove all speed records belonging to <?=$Username?> from stored records"><img src="static/common/symbols/trash.png" alt="del records" /></a>
                              &nbsp;&nbsp;
<?                          if ($ListType=='watchlist') {  ?>
                              <input type="button" onclick="watchlist_remove('<?=$UserID?>',true);return false;" value="Remove" title="Remove user from watchlist" />
<?                          } else {?>
                              <input type="button" onclick="excludelist_remove('<?=$UserID?>',true);return false;" value="Remove" title="Remove user from exclude list" />
<?                          } ?>
                            </td>
                    </tr>
<?              }
            }
?>
    </table>
<?
    //return $Userlist;
}

?>