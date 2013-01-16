    
<?
 


function print_speed_option($speed, $selected_speed){
?>
    <option value="<?=$speed?>" <?=($selected_speed==$speed?' selected="selected"':'');?>>&nbsp;<?=get_size($speed);?>/s&nbsp;&nbsp;</option>
<?
}


function print_user_notcheatslist($returnto) {
    global $DB;
    $DB->query("SELECT wl.UserID, um.Username, StaffID, um2.Username AS Staffname, Time, wl.Comment, 
                                 ui.Donor, ui.Warned, um.Enabled, um.PermissionID
                  FROM users_not_cheats AS wl
             LEFT JOIN users_main AS um ON um.ID=wl.UserID
             LEFT JOIN users_info AS ui ON ui.UserID=wl.UserID
             LEFT JOIN users_main AS um2 ON um2.ID=wl.StaffID
              ORDER BY Time DESC");
    
    $Userlist = $DB->to_array('UserID');
    return print_user_list($Userlist,'excludelist','Exclude users list','watchedgreen', 
            'Users in this list will be excluded from the multiban function and will not be shown on the cheats page',$returnto);
}


function print_user_watchlist($returnto) {
    global $DB;
    $DB->query("SELECT wl.UserID, um.Username, StaffID, um2.Username AS Staffname, Time, wl.Comment, 
                                 ui.Donor, ui.Warned, um.Enabled, um.PermissionID
                  FROM users_watch_list AS wl
             LEFT JOIN users_main AS um ON um.ID=wl.UserID
             LEFT JOIN users_info AS ui ON ui.UserID=wl.UserID
             LEFT JOIN users_main AS um2 ON um2.ID=wl.StaffID
              ORDER BY Time DESC");
    
    $Watchlist = $DB->to_array('UserID');
    return print_user_list($Watchlist,'watchlist','User watch list','watchedred', 'Users in the watch list will have their records retained until they are manually deleted. You can use this information to help detect ratio cheaters.<br/>
                    note: use the list sparingly - this can quickly fill the database with a huge number of records.',$returnto);
}




function print_user_list($Userlist,$ListType,$Title,$TitleIcon,$Help,$Returnto) {
    
?> 
        <div class="head"><?=$Title?> &nbsp;<img src="static/common/symbols/<?=$TitleIcon?>.png" alt="view" /><span style="float:right;"><a href="#" onclick="$('#<?=$ListType?>').toggle();this.innerHTML=this.innerHTML=='(hide)'?'(view)':'(hide)';">(view)</a></span>&nbsp;</div>
        <table id="<?=$ListType?>" class="hidden">
            <tr class="rowa"> 
                <td colspan="6" style="text-align: left;color:grey"> 
                    <?=$Help?>
                </td>
            </tr>
            <tr class="colhead">
                <td class="center"></td>
                <td class="center">User</td>
                <td class="center">Time added</td>
                <td class="center">added by</td>
                <td class="center">comment</td>
                <!--<td class="center" width="100px" title="keep torrent records related to this user">keep torrents</td>-->
                <td class="center" width="120px"></td>
            </tr>
<?
            $row = 'a';
            if(count($Userlist)==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="7">no users on watch list</td>
                    </tr>
<?
            } else {
                foreach ($Userlist as $Watched) {
                    list($UserID, $Username, $StaffID, $Staffname, $Time, $Comment,
                                       $IsDonor, $Warned, $Enabled, $ClassID) = $Watched;
                    $row = ($row === 'b' ? 'a' : 'b');
?> 
    
                    <tr class="row<?=$row?>">
                      <!--  <form action="tools.php" method="post">
                            <input type="hidden" name="action" value="edit_userwl" />
                            <input type="hidden" name="viewspeed" value="<?=$ViewSpeed?>" />
                            <input type="hidden" name="userid" value="<?=$UserID?>" /> 
                            <input type="hidden" name="returnto" value="<?=$Returnto?>" /> 
                            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" /> -->
                            <td class="center">
                                <a href="?action=speed_records&viewspeed=<?=$ViewSpeed?>&userid=<?=$UserID?>" title="View records for just <?=$Username?>"><img src="static/common/symbols/view.png" alt="view" /></a>
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
                              <input type="button" onclick="watchlist_remove('<?=$UserID?>',true)" value="Remove" title="Remove user from watchlist" />
<?                          } else {?>
                              <input type="button" onclick="excludelist_remove('<?=$UserID?>',true)" value="Remove" title="Remove user from exclude list" />
<?                          } ?>
                                <!--<input type="submit" name="submit" value="Save" title="Save edited value" />
                                <input type="submit" name="submit" value="Delete records" title="Remove all of this users records from the saved speed records" /> -->
                               <!-- <input type="submit" name="submit" value="Remove" title="Remove user from watchlist" />  -->
                            </td>
                      <!--  </form> -->
                    </tr>
<?              }
            }
?>
    </table>
    <br/>   
<?
    return $Userlist;
}

?>