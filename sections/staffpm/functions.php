<?
function get_num_staff_pms($UserID, $UserLevel){
        global $DB, $Cache;  
        //$NumUnanswered = $Cache->get_value('num_staff_pms_'.$UserID);
        //if ($NumUnanswered === false) {
            $DB->query("SELECT COUNT(ID) FROM staff_pm_conversations 
                                 WHERE (AssignedToUser=$UserID OR Level <=$UserLevel) AND Status='Unanswered'");
            list($NumUnanswered) = $DB->next_record();
            //$Cache->cache_value('num_staff_pms_'.$UserID, $NumUnanswered , 1000);
        //}
        //$NumOpen = $Cache->get_value('num_staff_pms_open_'.$UserID);
        //if ($NumOpen === false) {
            $DB->query("SELECT COUNT(ID) FROM staff_pm_conversations 
                                 WHERE (AssignedToUser=$UserID OR Level <=$UserLevel) AND Status IN ('Open', 'Unanswered')");
            list($NumOpen) = $DB->next_record();
            //$Cache->cache_value('num_staff_pms_open_'.$UserID, $NumOpen , 1000);
        //}
        //$NumMy = $Cache->get_value('num_staff_pms_my_'.$UserID);
        //if ($NumMy === false) {
            $DB->query("SELECT COUNT(ID) FROM staff_pm_conversations 
                                 WHERE (AssignedToUser=$UserID OR Level =$UserLevel) AND Status='Unanswered'");
            list($NumMy) = $DB->next_record();
            //$Cache->cache_value('num_staff_pms_my_'.$UserID, $NumMy , 1000);
        //}
        return array($NumMy, $NumUnanswered, $NumOpen);
}



function print_compose_staff_pm($Hidden = true, $Assign = 0, $Subject ='', $Msg = '', $Text = false) { 
        global $LoggedUser;  
        if (!$Text){
            include(SERVER_ROOT.'/classes/class_text.php');
            $Text = new TEXT;
        }
        if ($Msg=='changeusername'){
            $Subject='Change Username';
            $Msg="\n\nI would like to change my username to\n\nBecause";
            $Assign='admin';
        }
        else if ($Msg=='donategb' || $Msg=='donatelove'){
            $Subject='I would like to donate for ';
            if($Msg=='donategb') {
                $Subject .= 'GB';
                $Msg="\n\nPlease send me instructions on how to donate to remove gb from my download.";
            } else {
                $Subject .= 'love';
                $Msg="\n\nPlease send me instructions on how to donate to help support the site.";
            }
            $Assign='sysop';
            $AssignDirect = '1000';
        } else if ($Msg=='nobtcrate'){
            $Subject='Error: No exchange rate for bitcoin';
            $Msg='';
            $Assign='admin';
        }
       
        ?>
		<div id="compose" class="<?=($Hidden ? 'hide' : '')?>">
             <? if ( $LoggedUser['SupportFor'] !="" || $LoggedUser['DisplayStaff'] == 1 ) {  ?>
                    <div class="box pad">
                      <strong class="important_text">Are you sure you want to send a message to staff? You are staff yourself you know...</strong>
                    </div>
             <? }  ?>
                    <div id="preview" class="hidden"></div>
                    <form action="staffpm.php" method="post" id="messageform">
                    <div id="quickpost">  
				<input type="hidden" name="action" value="takepost" />
				<input type="hidden" name="prependtitle" value="Staff PM - " />
                                          
				<label for="subject"><h3>Subject</h3></label>
				<input class="long" type="text" name="subject" id="subject" value="<?=display_str($Subject)?>" />
				<br />
				
				<label for="message"><h3>Message</h3></label>
                            <? $Text->display_bbcode_assistant("message"); ?>
				<textarea rows="10" class="long" name="message" id="message"><?=display_str($Msg)?></textarea>
				<br />
				
                    </div>
				<input type="button" value="Hide" onClick="jQuery('#compose').toggle();return false;" />
				<strong>Send to: </strong>
<?                  if($AssignDirect){ ?>
				<input type="hidden" name="level" value="<?=$AssignDirect?>" />
				<input type="text" value="<?=$Assign?>" disabled="disabled" />
<?                  } else { ?>
				<select name="level">
					<option value="0"<?if(!$Assign)echo ' selected="selected"';?>>First Line Support</option>
					<option value="500"<?if($Assign=='mod')echo ' selected="selected"';?>>Mod Pervs</option>
					<option value="600"<?if($Assign=='admin')echo ' selected="selected"';?>>Admins</option>
					<!-- <option value="1000"<?if($Assign=='sysop')echo ' selected="selected"';?>>Sysop</option> -->
				</select>
<?                  } ?>
				<input type="button" id="previewbtn" value="Preview" onclick="Inbox_Preview();" /> 
                        <input type="submit" value="Send message" />
                    
			</form>
		</div>
<? } ?>
