<?
if(!check_perms('torrents_review')) { error(403); }

//include(SERVER_ROOT.'/sections/tools/managers/mfd_functions.php');

$ViewStatus = isset($_REQUEST['viewstatus'])?$_REQUEST['viewstatus']:'both';
$ViewStatus = in_array($ViewStatus, array('warned','pending','both'))?$ViewStatus:'pending';
$OverdueOnly = (isset($_REQUEST['overdue']) && $_REQUEST['overdue'])?1:0;

$DB->query("SELECT ReviewHours, AutoDelete FROM site_options LIMIT 1");
list($Hours, $Delete) = $DB->next_record();

$CanManage = check_perms('torrents_review_manage');
$NumOverdue = get_num_overdue_torrents('both');
if ($NumOverdue) // 
    $NumWarnedOverdue = get_num_overdue_torrents('warned');
else $OverdueOnly = 0;

show_header('Manage torrents marked for deletion');
  
?>
    <div class="thin">
        <h2>Torrents marked for deletion</h2>
<?
	if($NumDeleted) {
          $ResultMessage ="Successfully Deleted $NumDeleted Torrent";
          if($NumDeleted>1) $ResultMessage .= 's';
          if($ResultMessage){
?>
			<div id="messagebar" class="messagebar"><?=$ResultMessage?></div><br />
<?
          }
      }
?>

        <table class="box pad wid740">
            <tr class="colhead"><td colspan="3" class="center">site settings</td></tr>
            <tr>
                <form action="tools.php?action=marked_for_deletion" method="post">
                        <input type="hidden" name="action" value="save_mfd_options" />
                        <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
                        <input type="hidden" name="viewstatus" value="<?=$ViewStatus?>" />
                        <input type="hidden" name="overdue" value="<?=$OverdueOnly?>" />
                        
                        <td class="center">
                            <label for="hours">Warning period: (hours) </label>
<? if($CanManage){ ?> 
                            <input name="hours" type="text" style="width:30px;" value="<?=$Hours?>" title="This is the hours given to fix the torrent when warned (has no effect on current list)" />
<? } else { ?> 
                            <input name="hours" type="text" style="width:30px;color:black;" disabled="disabled" value="<?=$Hours?>" title="This is the hours given to fix the torrent when warned (has no effect on current list)" />
<? }  ?> 
                        </td>
                        <td  class="center">    
                            <label for="autodelete" title="AutoDelete">Auto Delete</label>
<? if($CanManage){ ?> 
                            <select id="autodelete" name="autodelete" title="If On then marked torrents are automatically deleted when they time out (if not pending). If Off then overdue marked torrents can still be deleted manually in this page.">
                                <option value="1"<?=$Delete?' selected="selected"':'';?>>On&nbsp;&nbsp;</option> 
                                <option value="0"<?=$Delete?'':' selected="selected"';?>>Off&nbsp;&nbsp;</option> 
                            </select>
<? } else { ?> 
                            <input type="text" name="autodelete" style="width:30px;color:black;" disabled="disabled" value="<?=$Delete?'On':'Off';?>" title="If On then marked torrents are automatically deleted when they time out (if not pending). If Off then overdue marked torrents can still be deleted manually in this page." />
<? }  ?> 
                        </td>
<? if($CanManage){ ?>   <td  class="center">  <!-- width="30%" -->
                            <input type="submit" value="Save Changes" />
                        </td> <? }  ?>
                </form>
            </tr>
        </table>
                  
        <div class="linkbox" >
        
 
<?      if ($ViewStatus!='warned'){   ?>
          [<a href="tools.php?action=marked_for_deletion&amp;viewstatus=warned&amp;overdue=<?=$OverdueOnly?>"> View warned only </a>] &nbsp;&nbsp;&nbsp;
<?      }
        if ($ViewStatus!='pending'){   ?>
          [<a href="tools.php?action=marked_for_deletion&amp;viewstatus=pending&amp;overdue=<?=$OverdueOnly?>"> View pending only </a>] &nbsp;&nbsp;&nbsp;
<?      }
        if ($ViewStatus!='both'){   ?>
          [<a href="tools.php?action=marked_for_deletion&amp;viewstatus=both&amp;overdue=<?=$OverdueOnly?>"> View pending and warned </a>] &nbsp;&nbsp;&nbsp;
<?      }       ?>
    
<?      if ($NumOverdue) {
            if ($OverdueOnly){  ?>
          [<a href="tools.php?action=marked_for_deletion&amp;viewstatus=<?=$ViewStatus?>&amp;overdue=0"> View due and overdue </a>] &nbsp;&nbsp;&nbsp;
<?          } else {     ?>
          [<a href="tools.php?action=marked_for_deletion&amp;viewstatus=<?=$ViewStatus?>&amp;overdue=1"> View overdue only </a>] &nbsp;&nbsp;&nbsp;
<?          }
        } ?>

        </div>
        <br/>
<?       
        $Torrents = get_torrents_under_review($ViewStatus, $OverdueOnly);
        $NumTorrents = count($Torrents);
            
?>
        <form method="post" action="tools.php" id="reviewform">
            <div class="head">Marked for Deletion Mass Actions</div>
            <div class="box pad">
                <h3 style="float:right;margin:5px 10px 0 0;">Showing: <?=$ViewStatus=='both'?'pending and warned':$ViewStatus;?> <?=$OverdueOnly?'(overdue only)':'';?></h3>
<?      if ($NumOverdue) {
 		if ($CanManage) {  // not sure who should have what permissions here??    ?> 
                <span style="position:absolute;">
                    <input type="submit" name="submit" title="Delete selected torrents" value="Delete selected" />
                </span>     
<?		} 
            if ($NumWarnedOverdue) {  ?> 
                <!-- anyone with torrents_review permission can delete warned and overdue torrents  -->
                <input type="submit" name="submitdelall" style="width:350px;margin-left:-175px;left:50%;position:relative;" title="Delete <?=$NumWarnedOverdue?> Warned and Overdue torrents (red background)" value="Delete <?=$NumWarnedOverdue?> Warned and Overdue torrents" />
                    
<?          }   ?> 
<?      }   ?> 
                <br style="clear:both" />
             </div> 
        
            <div class="head">Torrents Marked for Deletion</div>
            <table>
                <tr class="colhead">
<? if($NumOverdue && $CanManage){ ?><td width="8px"><input type="checkbox" onclick="toggleChecks('reviewform',this)" /></td><? } ?>
                    <td>Torrent</td>
                    <td width="40px"><strong>Status</strong></td>
                    <td>time till nuke</td>
                    <td><strong>Reason</strong>
                    <td>Username</td>
                </tr>
<?
    if ($NumTorrents==0){ // 
?>
                <tr>
                    <td colspan="6" class="center">no torrents are under review</td>
                </tr>
<? 
    } else {
        $Row = 'a';
        foreach($Torrents as $Torrent){
            $Row = $Row=='a'?'b':'a';
            list($TorrentID, $GroupID, $TorrentName, $Status, $ConvID, $KillTime, $Reason, $UserID, $Username) = $Torrent;
            
            $IsOverdue = strtotime($KillTime)<time();
?>
                <tr class="<?=($IsOverdue?($Status=='Pending'?'orangebar':'redbar'):"row$Row")?>">
<? if($NumOverdue && $CanManage){ ?><td class="center"><?=$IsOverdue?'<input type="checkbox" name="id[]" value="'.$GroupID.'" />':''?></td><? } ?>
                    <td><a href="torrents.php?id=<?=$GroupID?>"><?=$TorrentName?></a></td>
                    <td><?=$Status?></td>
                    <td><?=time_diff($KillTime)?></td>
                    <td><?=$Reason?>
<?
        if ($ConvID>0) {
                echo '<span style="float:right;">'.($Status=='Pending'?'(user sent fixed message) &nbsp;&nbsp;':'').'<a href="staffpm.php?action=viewconv&id='.$ConvID.'">'.($Status=='Pending'?'Message sent to staff':"reply sent to $Username").'</a></span>';
        } elseif ($Status == 'Warned') {
                echo '<span style="float:right;">(pm sent to '.$Username.')</span>';
        }
?>
                    </td>
                    <td><?=format_username($UserID, $Username)?></td>
                </tr>      
<?
        }
    } // end print table of warned torrents    
?>
                <input type="hidden" name="viewstatus" value="<?=$ViewStatus?>" />
                <input type="hidden" name="overdue" value="<?=$OverdueOnly?>" />
                <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
                <input type="hidden" name="action" value="mfd_delete" />
            
            </table>
        </form>
        <br/>
        
        
        <div class="head">Statistics</div>
        <div class="box pad">
                
        <table>
        <tr>
            <td style="width: 50%;">
<? 

$BaseSQL = "SELECT um.ID, 
                   um.Username, 
                   COUNT(r.ID) AS Num , 
                   (SELECT Count(torrents_reviews.ID) 
                                         FROM torrents_reviews 
                                         WHERE torrents_reviews.UserID=um.ID
                                           AND torrents_reviews.Status='Okay'
                                           [TIMECLAUSE] ) AS NumOkay,
                   (SELECT Count(torrents_reviews.ID) 
                                         FROM torrents_reviews 
                                         WHERE torrents_reviews.UserID=um.ID
                                           AND torrents_reviews.Status='Warned'
                                           [TIMECLAUSE] ) AS NumWarned
              FROM torrents_reviews AS r JOIN users_main AS um ON um.ID=r.UserID 
             WHERE r.Status!='Pending'  [TIMECLAUSE]
          GROUP BY r.UserID 
          ORDER BY Num DESC";


$DB->query(str_replace('[TIMECLAUSE]', 'AND r.Time > NOW() - INTERVAL 24 HOUR', $BaseSQL));
$Results = $DB->to_array();

?>
            <strong>MFD actions in the last 24 hours</strong>
            <table class="noborder">
                <tr class="colhead">
                    <td>Username</td>
                    <td>Total</td>
                    <td>Okay</td>
                    <td>Warned</td>
                </tr>
<? foreach($Results as $Result) {
	list($UserID, $Username, $Num, $NumOkay, $NumWarned) = $Result;
?>
                <tr>
                    <td><a href="reportsv2.php?view=resolver&amp;id=<?=$UserID?>"><?=$Username?></a></td>
                    <td><?=$Num?></td>
                    <td><?=$NumOkay?></td>
                    <td><?=$NumWarned?></td>
                </tr>
<? } ?>
            </table>
            <br /><br />
<?

$DB->query(str_replace('[TIMECLAUSE]', 'AND r.Time > NOW() - INTERVAL 1 WEEK', $BaseSQL));
$Results = $DB->to_array();

?>
            <strong>MFD actions in the last week</strong>
            <table class="noborder">
                <tr class="colhead">
                    <td>Username</td>
                    <td>Total</td>
                    <td>Okay</td>
                    <td>Warned</td>
                </tr>
<? foreach($Results as $Result) {
	list($UserID, $Username, $Num, $NumOkay, $NumWarned) = $Result;
?>
                <tr>
                    <td><a href="reportsv2.php?view=resolver&amp;id=<?=$UserID?>"><?=$Username?></a></td>
                    <td><?=$Num?></td>
                    <td><?=$NumOkay?></td>
                    <td><?=$NumWarned?></td>
                </tr>
<? } ?>
            </table>
		
        </td>
        <td>
<?

$DB->query(str_replace('[TIMECLAUSE]', 'AND r.Time > NOW() - INTERVAL 1 MONTH', $BaseSQL));
$Results = $DB->to_array();

?>
		<strong>MFD actions in the last month</strong>
		<table class="noborder">
			<tr class="colhead">
				<td>Username</td>
				<td>Total</td>
				<td>Okay</td>
				<td>Warned</td>
			</tr>
<? foreach($Results as $Result) {
	list($UserID, $Username, $Num, $NumOkay, $NumWarned) = $Result;
?>
			<tr>
				<td><a href="reportsv2.php?view=resolver&amp;id=<?=$UserID?>"><?=$Username?></a></td>
				<td><?=$Num?></td>
				<td><?=$NumOkay?></td>
				<td><?=$NumWarned?></td>
			</tr>
<? } ?>
		</table>
		<br /><br />
<?

$DB->query(str_replace('[TIMECLAUSE]', '', $BaseSQL));
$Results = $DB->to_array();

?>
            <strong>MFD actions total</strong>
            <table class="noborder">
                <tr class="colhead">
                    <td>Username</td>
                    <td>Total</td>
                    <td>Okay</td>
                    <td>Warned</td>
                </tr>
<? foreach($Results as $Result) {
	list($UserID, $Username, $Num, $NumOkay, $NumWarned) = $Result;
?>
                <tr>
                    <td><a href="reportsv2.php?view=resolver&amp;id=<?=$UserID?>"><?=$Username?></a></td>
                    <td><?=$Num?></td>
                    <td><?=$NumOkay?></td>
                    <td><?=$NumWarned?></td>
                </tr>
<? } ?>
            </table>
		
        </td></tr>
        </table>
   
        </div>
 
        <br/>
    </div>

<? show_footer(); ?>
