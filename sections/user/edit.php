<?

$UserID = $_REQUEST['userid'];
if(!is_number($UserID)){
	error(404);
}

 
$DB->query("SELECT 
			m.Username,
			m.Email,
			m.IRCKey,
			m.Paranoia,
                  m.Signature,
                  m.PermissionID,
                    m.CustomPermissions,
			i.Info,
			i.Avatar,
			i.Country,
			i.StyleID,
			i.StyleURL,
			i.SiteOptions,
			i.UnseededAlerts,
                  i.TimeZone,
            m.Flag
			FROM users_main AS m
			JOIN users_info AS i ON i.UserID = m.ID
			LEFT JOIN permissions AS p ON p.ID=m.PermissionID
			WHERE m.ID = '".db_string($UserID)."'");

list($Username,$Email,$IRCKey,$Paranoia,$Signature,$PermissionID,$CustomPermissions,$Info,$Avatar,$Country,
        $StyleID,$StyleURL,$SiteOptions,$UnseededAlerts,$TimeZone,$flag)=$DB->next_record(MYSQLI_NUM, array(3,6,12));

$Permissions = get_permissions($PermissionID);
list($Class,$PermissionValues,$MaxSigLength,$MaxAvatarWidth,$MaxAvatarHeight)=array_values($Permissions);

if($UserID != $LoggedUser['ID'] && !check_perms('users_edit_profiles', $Class)) {
	error(403);
}

$Paranoia = unserialize($Paranoia);
if(!is_array($Paranoia)) $Paranoia = array(); 
  

function paranoia_level($Setting) {
       global $Paranoia;
       // 0: very paranoid; 1: stats allowed, list disallowed; 2: not paranoid
       return (in_array($Setting . '+', $Paranoia)) ? 0 : (in_array($Setting, $Paranoia) ? 1 : 2);
}

function display_paranoia($FieldName) {
       $Level = paranoia_level($FieldName);
       print '<label><input type="checkbox" name="p_'.$FieldName.'_c" '.checked($Level >= 1).' onChange="AlterParanoia()" /> Show count</label>'."&nbsp;&nbsp;\n";
       print '<label><input type="checkbox" name="p_'.$FieldName.'_l" '.checked($Level >= 2).' onChange="AlterParanoia()" /> Show list</label>';
}

function checked($Checked) {
	return $Checked ? 'checked="checked"' : '';
}
 

function sorttz($a, $b) {
    if ($a[1] == $b[1]) {
        if ($a[0] == $b[0]) {
            return 0;
        } else {
            return ($a[0] < $b[0]) ? -1 : 1;
        }
    } else {
        return ($a[1] < $b[1]) ? -1 : 1;
    }
}



function get_timezones_list(){ 
    global $Cache; 
    $zones = $Cache->get_value('timezones');
    if ($zones !== false) return $zones;
    $rawzones = timezone_identifiers_list();
    $Continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic','Australia','Europe','Indian','Pacific');
    $i = 0;
    foreach($rawzones AS $szone) {
        $z = explode('/',$szone);
        if( in_array($z[0], $Continents )){      
            $zones[$i][0] = $szone;
            $zones[$i][1] = -get_timezone_offset($szone);    // format_offset(-get_timezone_offset($szone));
            $i++;
        }
    }
    usort($zones, "sorttz");
    foreach($zones AS &$zone) {
        $zone[1] = format_offset($zone[1]);
    }
    $Cache->cache_value('timezones', $zones);
    return $zones;
}

function format_offset($offset) {
        $hours = $offset / 3600;
        $sign = $hours > 0 ? '+' : '-';
        $hour = (int) abs($hours);
        $minutes = (int) abs(($offset % 3600) / 60); // for stupid half hour timezones
        if ($hour == 0 && $minutes == 0) $sign = '&nbsp;';
        return "GMT $sign" . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes,2, '0'); 
}

 
$flags = scandir(SERVER_ROOT.'/static/common/flags/64', 0);
$flags= array_diff($flags, array('.','..'));


$DB->query("SELECT COUNT(x.uid) FROM xbt_snatched AS x INNER JOIN torrents AS t ON t.ID=x.fid WHERE x.uid='$UserID'");
list($Snatched) = $DB->next_record();

if ($SiteOptions) { 
	$SiteOptions = unserialize($SiteOptions); 
} else { 
	$SiteOptions = array();
}
if (!isset($SiteOptions['MaxTags'])) $SiteOptions['MaxTags'] = 100;

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

show_header($Username.' > Settings','user,validate,bbcode,jquery,jquery.cookie');
echo $Val->GenerateJS('userform');
?>
 
<div class="thin">
    <h2>User Settings</h2>
	<div class="head"><?=format_username($UserID,$Username)?> &gt; Settings</div>
	<form id="userform" name="userform" action="" method="post" onsubmit="return formVal();" autocomplete="off">
		<div>
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="userid" value="<?=$UserID?>" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		</div>
		<table cellpadding='6' cellspacing='1' border='0' width='100%' class='border'>
			<tr class="colhead">
				<td colspan="2">
					<strong>Site preferences</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Stylesheet</strong></td>
				<td>
					<select name="stylesheet" id="stylesheet">
<? foreach($Stylesheets as $Style) { ?>
						<option value="<?=$Style['ID']?>"<? if ($Style['ID'] == $StyleID) { ?>selected="selected"<? } ?>><?=$Style['ProperName']?></option>
<? } ?>
					</select>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Or -&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					External CSS: <input type="text" size="40" name="styleurl" id="styleurl" value="<?=display_str($StyleURL)?>" />
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Time Zone</strong></td>
				<td> 
                            <select name="timezone" id="timezone">
<?                          
                                    $zones = get_timezones_list();
                                    foreach($zones as $tzone) { 
                                        list($zone,$offset)=$tzone;
                                        //$offset = format_offset($offset);
?>                              <option value="<?=$zone?>"<? if ($zone == $TimeZone) { ?>selected="selected"<? } ?>><?="$offset &nbsp;&nbsp;".str_replace(array('_','/'),array(' ',' / '),$zone)?></option>
<?                                  } ?>
                            </select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Time style</strong></td>
				<td>
					<input type="radio" name="timestyle" value="0" <? if (empty($LoggedUser['TimeStyle'])||$LoggedUser['TimeStyle']==0) { ?>checked="checked"<? } ?> />
					<label>Display times as time since (date and time is displayed as tooltip)</label><br/>
					<input type="radio" name="timestyle" value="1" <? if ( $LoggedUser['TimeStyle']==1) { ?>checked="checked"<? } ?> />
					<label>Display times as date and time (time since is displayed as tooltip)</label>
				</td>
			</tr>
<? if (check_perms('site_advanced_search')) { ?>
			<tr>
				<td class="label"><strong>Default Search Type</strong></td>
				<td>
					<select name="searchtype" id="searchtype">
						<option value="0"<? if ($SiteOptions['SearchType'] == 0) { ?>selected="selected"<? } ?>>Simple</option>
						<option value="1"<? if ($SiteOptions['SearchType'] == 1) { ?>selected="selected"<? } ?>>Advanced</option>
					</select>
				</td>
			</tr>
<? } ?>
			<tr>
				<td class="label"><strong>Torrents per page</strong></td>
				<td>
					<select name="torrentsperpage" id="torrentsperpage">
						<option value="25"<? if ($SiteOptions['TorrentsPerPage'] == 25) { ?>selected="selected"<? } ?>>25</option>
						<option value="50"<? if ($SiteOptions['TorrentsPerPage'] == 50) { ?>selected="selected"<? } ?>>50 (Default)</option>
						<option value="100"<? if ($SiteOptions['TorrentsPerPage'] == 100) { ?>selected="selected"<? } ?>>100</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Posts per page (Forum)</strong></td>
				<td>
					<select name="postsperpage" id="postsperpage">
						<option value="25"<? if ($SiteOptions['PostsPerPage'] == 25) { ?>selected="selected"<? } ?>>25 (Default)</option>
						<option value="50"<? if ($SiteOptions['PostsPerPage'] == 50) { ?>selected="selected"<? } ?>>50</option>
						<option value="100"<? if ($SiteOptions['PostsPerPage'] == 100) { ?>selected="selected"<? } ?>>100</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Collage torrent covers to show per page</strong></td>
				<td>
					<select name="collagecovers" id="collagecovers">
						<option value="10"<? if ($SiteOptions['CollageCovers'] == 10) { ?>selected="selected"<? } ?>>10</option>
						<option value="25"<? if (($SiteOptions['CollageCovers'] == 25) || !isset($SiteOptions['CollageCovers'])) { ?>selected="selected"<? } ?>>25 (default)</option>
						<option value="50"<? if ($SiteOptions['CollageCovers'] == 50) { ?>selected="selected"<? } ?>>50</option>
						<option value="100"<? if ($SiteOptions['CollageCovers'] == 100) { ?>selected="selected"<? } ?>>100</option>
						<option value="1000000"<? if ($SiteOptions['CollageCovers'] == 1000000) { ?>selected="selected"<? } ?>>All</option>
						<option value="0"<? if (($SiteOptions['CollageCovers'] === 0) || (!isset($SiteOptions['CollageCovers']) && $SiteOptions['HideCollage'])) { ?>selected="selected"<? } ?>>None</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Split Torrents by Days</strong></td>
				<td>
					<input type="checkbox" name="splitbydays" id="splitbydays" <? if (!empty($SiteOptions['SplitByDays'])) { ?>checked="checked"<? } ?> />
					<label for="splitbydays">display new day header in browse torrents list</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Category list in torrent search</strong></td>
				<td>
					<select name="hidecats" id="hidecats">
						<option value="0"<? if ($SiteOptions['HideCats'] == 0) { ?>selected="selected"<? } ?>>Open by default.</option>
						<option value="1"<? if ($SiteOptions['HideCats'] == 1) { ?>selected="selected"<? } ?>>Closed by default.</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Tag list in torrent search</strong></td>
				<td>
					<select name="showtags" id="showtags">
						<option value="1"<? if ($SiteOptions['ShowTags'] == 1) { ?>selected="selected"<? } ?>>Open by default.</option>
						<option value="0"<? if ($SiteOptions['ShowTags'] == 0) { ?>selected="selected"<? } ?>>Closed by default.</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Tags in lists</strong></td>
				<td>
					<input type="checkbox" name="hidetagsinlists" id="hidetagsinlists" <? if (!empty($SiteOptions['HideTagsInLists'])) { ?>checked="checked"<? } ?> />
					<label for="hidetagsinlists">Hide tags in lists</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Max Tags in lists</strong></td>
				<td> <? //if (!isset($SiteOptions['MaxTags'])) $SiteOptions['MaxTags']=20; ?>
					<input type="text" name="maxtags" id="maxtags" size="8" value="<?=((int)$SiteOptions['MaxTags'])?>" />
					<label for="maxtags">The maximum number of tags to show in a torrent list</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Hover info window</strong></td>
				<td>
					<input type="checkbox" name="hidefloatinfo" id="hidefloatinfo" <? if (!empty($SiteOptions['HideFloat'])) { ?>checked="checked"<? } ?> />
					<label for="hidetagsinlists">Hide floating info window on browse torrents page</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>External link behaviour</strong></td>
				<td>
					<input type="checkbox" name="forcelinks" id="forcelinks" <? if (empty($SiteOptions['NotForceLinks'])) { ?>checked="checked"<? } ?> />
					<label for="forcelinks">Force external links to open in a new page</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Add tag behaviour</strong></td>
				<td>
					<input type="checkbox" name="voteuptags" id="voteuptags" <? if (empty($SiteOptions['NotVoteUpTags'])) { ?>checked="checked"<? } ?> />
					<label for="voteuptags">Automatically vote up my added tags</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Accept PM's</strong></td>
				<td>
					<input type="radio" name="blockPMs" id="blockPMs" value="0" <? if (empty($LoggedUser['BlockPMs'])||$LoggedUser['BlockPMs']==0) { ?>checked="checked"<? } ?> />
					<label>All (except blocks)</label><br/>
					<input type="radio" name="blockPMs" id="blockPMs" value="1" <? if ( $LoggedUser['BlockPMs']==1) { ?>checked="checked"<? } ?> />
					<label>Friends only</label><br/>
					<input type="radio" name="blockPMs" id="blockPMs" value="2"  <? if ($LoggedUser['BlockPMs']==2 ) { ?>checked="checked"<? } ?> />
					<label>Staff only</label>
					
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Comments PM</strong></td>
				<td>
					<input type="checkbox" name="commentsnotify" id="commentsnotify" <? if (!empty($LoggedUser['CommentsNotify'])) { ?>checked="checked"<? } ?> />
					<label for="commentsnotify">Notify me by PM when I receive a comment on one of my torrents</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Subscription</strong></td>
				<td>
					<input type="checkbox" name="autosubscribe" id="autosubscribe" <? if (!empty($SiteOptions['AutoSubscribe'])) { ?>checked="checked"<? } ?> />
					<label for="autosubscribe">Subscribe to topics when posting</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Page Titles</strong></td>
				<td>
					<input type="checkbox" name="shortpagetitles" id="shortpagetitles" <? if (!empty($SiteOptions['ShortTitles'])) { ?>checked="checked"<? } ?> />
					<label for="shortpagetitles">Use short page titles (ie. instead of Forums > Forum-name > Thread-title use just Thread-Title)</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Forum topics</strong></td>
				<td>
					<input type="checkbox" name="disablelatesttopics" id="disablelatesttopics" <? if (!empty($SiteOptions['DisableLatestTopics'])) { ?>checked="checked"<? } ?> />
					<label for="disablelatesttopics">Disable latest forum topics</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>User Torrents</strong></td>
				<td>
					<input type="checkbox" name="showusertorrents" id="showusertorrents" <? if (empty($SiteOptions['HideUserTorrents']) || $SiteOptions['HideUserTorrents']==0) { ?>checked="checked"<? } ?> />
					<label for="showusertorrents">Show users uploaded torrents on user page (if allowed by that users paranoia settings)</label>
				</td>
			</tr>
                  <tr>
				<td class="label"><strong>Smileys</strong></td>
				<td>
					<input type="checkbox" name="disablesmileys" id="disablesmileys" <? if (!empty($SiteOptions['DisableSmileys'])) { ?>checked="checked"<? } ?> />
					<label for="disablesmileys">Disable smileys</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Avatars</strong></td>
				<td>
					<input type="checkbox" name="disableavatars" id="disableavatars" <? if (!empty($SiteOptions['DisableAvatars'])) { ?>checked="checked"<? } ?> />
					<label for="disableavatars">Disable avatars (disabling avatars also hides user badges)</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Signatures</strong></td>
				<td>
					<input type="checkbox" name="disablesignatures" id="disablesignatures" <? if (!empty($SiteOptions['DisableSignatures'])) { ?>checked="checked"<? } ?> />
					<label for="disablesignatures">Disable Signatures</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Download torrents as text files</strong></td>
				<td>
					<input type="checkbox" name="downloadalt" id="downloadalt" <? if ($DownloadAlt) { ?>checked="checked"<? } ?> />
					<label for="downloadalt">For users whose ISP block the downloading of torrent files</label>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Unseeded torrent alerts</strong></td>
				<td>
					<input type="checkbox" name="unseededalerts" id="unseededalerts" <?=checked($UnseededAlerts)?> />
					<label for="unseededalerts">Receive a PM alert before your uploads are deleted for being unseeded</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" title="Save all changes" />
				</td>
			</tr>
            
			<tr class="colhead">
				<td colspan="2">
					<strong>User info</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Avatar URL</strong></td>
				<td>
					<input class="long" type="text" name="avatar" id="avatar" value="<?=display_str($Avatar)?>" />
					<p class="min_padding">Maximum Size: <?=$MaxAvatarWidth?>x<?=$MaxAvatarHeight?> pixels (will be resized if necessary)</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Flag</strong></td>
				<td style="">
                    <span id="flag_image" >
                        <? if ($flag && $flag != '??'){ ?>
                            <img src="/static/common/flags/64/<?=$flag?>.png" />
                        <? } ?>
                    </span>
                    <div style="display:inline-block;vertical-align: top;"> 
                        <select id="flag" name="flag" onchange="change_flag();" style="margin-top: 25px">
                            <option value="" <?=($flag == '' || $flag == '??') ? 'selected="selected"' : '';?>>none</option>
                   <?       foreach($flags as $value) {  
                                $value = substr($value, 0, strlen($value)-4  ); // remove .png extension
                    ?>
                            <option value="<?=display_str($value)?>" <?=($flag == $value) ? 'selected="selected"' : '';?>><?=$value?></option>
                    <?      }  ?>
                        </select>
                    </div>
                </td>
			</tr>
                    
<?      if ( check_perms('site_set_language') ) {  ?>
            
            <tr>
                <td class="label"><strong>Language(s)</strong></td>
                <td>           
<?    
     
                $Userlangs = $Cache->get_value('user_langs_' .$UserID);
                if($Userlangs===false){
                    $DB->query("SELECT ul.LangID, l.code, l.flag_cc AS cc, l.language  
                              FROM users_languages AS ul 
                              JOIN languages AS l ON l.ID=ul.LangID  
                             WHERE UserID=$UserID");
                    $Userlangs = $DB->to_array('LangID', MYSQL_ASSOC);
                    $Cache->cache_value('user_langs_'.$UserID, $Userlangs);
                }
                if($Userlangs) {
?> 
                    select language to remove it:<br/>
<?
                    foreach($Userlangs as $langresult) {
?>
                    <input type="checkbox" name="del_lang[]" value="<?=$langresult['LangID']?>" />
                        <img style="vertical-align: bottom" title="<?=$langresult['language']?>" alt="[<?=$langresult['code']?>]" src="http://<?=SITE_URL?>/static/common/flags/iso16/<?=$langresult['cc']?>.png" />
<?
                    }
?>
                     <br/>
<?
                }
                 
                $SiteLanguages = $Cache->get_value('site_languages');
                if($SiteLanguages===false){
                    $DB->query("SELECT ID, language FROM languages WHERE active='1' ORDER BY language");
                    $SiteLanguages = $DB->to_array('ID', MYSQL_ASSOC);
                    $Cache->cache_value('site_languages', $SiteLanguages);
                }
?>
                    <div style="display:inline-block;vertical-align: top;"> 
                        add language: 
                        <span id="lang_image"> 
                        </span>
                        <select id="new_lang" name="new_lang" onchange="change_lang_flag();" style="margin-top: 25px">
                            <option value="" selected="selected" >none</option>
                   <?       foreach($SiteLanguages as $key=>$value) {
                                if (!array_key_exists($key, $Userlangs)  ) { ?>
                                    <option value="<?=$key?>"><?=$value['language']?></option>
                   <?           }
                            }  ?>
                        </select>
                    </div>
                     <br/>(only staff can see your selected language) <br/>
                </td>
            </tr>
<?  
        }
?> 
			<tr>
				<td class="label"><strong>Email</strong></td>
				<td><input class="long" type="text" name="email" id="email" value="<?=display_str($Email)?>" />
					<p class="min_padding">If changing this field you must enter your current password in the "Current password" field before saving your changes.</p>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Info</strong></td>
				<td> 
				<div class="box pad hidden" id="preview_info" style="text-align:left;"></div>
				<div  class="" id="editor_info" >
                              <? $Text->display_bbcode_assistant("preview_message_info", get_permissions_advtags($UserID, unserialize($CustomPermissions),$Permissions )); ?>
                            <textarea id="preview_message_info" name="info" class="long" rows="8"><?=display_str($Info)?></textarea>
                        </div>
                        <input type="button" value="Toggle Preview" onclick="Preview_Toggle('info');" />
                        </td>
                  </tr>
			<tr>
				<td class="label"><strong>Signature<br/>(max <?=$MaxSigLength?> chars)<br/><span style="text-decoration: underline">max total size</span><br/>800 px * 300 px</strong></td>
				<td>
				<div class="box pad hidden" id="preview_sig" style="text-align:left;"></div>
				<div  class="" id="editor_sig" >
                          <? $Text->display_bbcode_assistant("preview_message_sig", get_permissions_advtags($UserID, unserialize($CustomPermissions),$Permissions )); ?>
                            <textarea  id="preview_message_sig" name="signature" class="long" 
                                      rows="<?=($MaxSigLength !== 0 ? round(3 + ($MaxSigLength / 512)) : 2);?>" 
                    <?=($MaxSigLength == 0 ? 'disabled="disabled"' : ''); ?>><?=($MaxSigLength == 0 ? 'You need to get promoted to Perv before you can have a signature!' : display_str($Signature));?></textarea>
                        </div>
                        <input type="button" value="Toggle Preview" onclick="Preview_Toggle('sig');" />
                        </td>
			</tr> 
			<tr>
				<td class="label"><strong>IRCKey</strong></td>
				<td>
					<input class="long" type="text" name="irckey" id="irckey" value="<?=display_str($IRCKey)?>" />
					<p class="min_padding">This field, if set will be used in place of the password in the IRC login.</p>
					<p class="min_padding">Note: This value is stored in plaintext and should not be your password.</p>
					<p class="min_padding">Note: In order to be accepted as correct, your IRCKey must be between 6 and 32 characters.</p>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" title="Save all changes" />
				</td>
			</tr>
			<tr class="colhead">
				<td colspan="2">
					<strong>Paranoia settings</strong>
				</td>
			</tr>
			<tr>
				<td class="label">&nbsp;</td>
				<td>
					<p><span class="warning">Note: Paranoia has nothing to do with your security on this site, the only thing affected by this setting is other users ability to see your site activity and taste.</span></p>
					<p>Select the elements <strong>you want to show</strong> on your profile. For example, if you tick "Show count" for "Snatched", users will be able to see that you have snatched <?=number_format($Snatched)?> torrents. If you tick "Show list", they will be able to see the full list of torrents you've snatched.</p>
					<p><span class="warning">Some information will still be available in the site log.</span></p>
				</td>
			</tr>
			<tr>
				<td class="label">Recent activity</td>
				<td>
					<label><input type="checkbox" name="p_lastseen" <?=checked(!in_array('lastseen', $Paranoia))?>> Last seen</label>
				</td>
			</tr>
			<tr>
				<td class="label">Preset</td>
				<td>
					<button type="button" onClick="ParanoiaResetOff()">Show everything</button>
					<button type="button" onClick="ParanoiaResetStats2()">Show all but snatches</button>
					<button type="button" onClick="ParanoiaResetStats()">Show stats only</button>
					<!--<button type="button" onClick="ParanoiaResetOn()">Show nothing</button>-->
				</td>
			</tr>
			<tr>
				<td class="label">Stats</td>
				<td>
<?
$UploadChecked = checked(!in_array('uploaded', $Paranoia));
$DownloadChecked = checked(!in_array('downloaded', $Paranoia));
$RatioChecked = checked(!in_array('ratio', $Paranoia));
?>
					<label><input type="checkbox" name="p_uploaded" onChange="AlterParanoia()"<?=$UploadChecked?> /> Uploaded</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_downloaded" onChange="AlterParanoia()"<?=$DownloadChecked?> /> Downloaded</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_ratio" onChange="AlterParanoia()"<?=$RatioChecked?> /> Ratio</label>
				</td>
			</tr>
			<tr>
				<td class="label">Torrent comments</td>
				<td>
<? display_paranoia('torrentcomments'); ?>
				</td>
			</tr>
			<tr>
				<td class="label">Collages started</td>
				<td>
<? display_paranoia('collages'); ?>
				</td>
			</tr>
			<tr>
				<td class="label">Collages contributed to</td>
				<td>
<? display_paranoia('collagecontribs'); ?>
				</td>
			</tr>
				<td class="label">Requests filled</td>
				<td>
<?
$RequestsFilledCountChecked = checked(!in_array('requestsfilled_count', $Paranoia));
$RequestsFilledBountyChecked = checked(!in_array('requestsfilled_bounty', $Paranoia));
$RequestsFilledListChecked = checked(!in_array('requestsfilled_list', $Paranoia));
?>
					<label><input type="checkbox" name="p_requestsfilled_count" onChange="AlterParanoia()" <?=$RequestsFilledCountChecked?> /> Show count</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_requestsfilled_bounty" onChange="AlterParanoia()" <?=$RequestsFilledBountyChecked?> /> Show bounty</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_requestsfilled_list" onChange="AlterParanoia()" <?=$RequestsFilledListChecked?> /> Show list</label>
				</td>
			</tr>
				<td class="label">Requests voted</td>
				<td>
<?
$RequestsVotedCountChecked = checked(!in_array('requestsvoted_count', $Paranoia));
$RequestsVotedBountyChecked = checked(!in_array('requestsvoted_bounty', $Paranoia));
$RequestsVotedListChecked = checked(!in_array('requestsvoted_list', $Paranoia));
?>
					<label><input type="checkbox" name="p_requestsvoted_count" onChange="AlterParanoia()" <?=$RequestsVotedCountChecked?> /> Show count</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_requestsvoted_bounty" onChange="AlterParanoia()" <?=$RequestsVotedBountyChecked?> /> Show bounty</label>&nbsp;&nbsp;
					<label><input type="checkbox" name="p_requestsvoted_list" onChange="AlterParanoia()" <?=$RequestsVotedListChecked?> /> Show list</label>
				</td>
			</tr>
			<tr>
				<td class="label" title="uploaded torrents">Uploaded</td>
				<td>
<? display_paranoia('uploads'); ?>
				</td>
			</tr>
			<tr>
				<td class="label" title="torrents you are currently seeding">Seeding</td>
				<td>
<? display_paranoia('seeding'); ?>
				</td>
			</tr>
			<tr>
				<td class="label" title="torrents you are currently leeching">Leeching</td>
				<td>
<? display_paranoia('leeching'); ?>
				</td>
			</tr>
			<tr>
				<td class="label" title="torrents you have downloaded 100% of">Snatched</td>
				<td>
<? display_paranoia('snatched'); ?>
				</td>
			</tr>
			<tr>
				<td class="label" title="torrent files you have downloaded">Grabbed</td>
				<td>
<? display_paranoia('grabbed'); ?>
				</td>
			</tr>
			<tr>
				<td class="label">Miscellaneous</td>
				<td>
					<label><input type="checkbox" name="p_requiredratio" <?=checked(!in_array('requiredratio', $Paranoia))?>> Required ratio</label>
<?
$DB->query("SELECT COUNT(UserID) FROM users_info WHERE Inviter='$UserID'");
list($Invited) = $DB->next_record();
?>
					<br /><label><input type="checkbox" name="p_invitedcount" <?=checked(!in_array('invitedcount', $Paranoia))?>> Number of users invited</label>
				 <!-- <br /><label><input type="checkbox" name="p_showbadges" <?=checked(!in_array('showbadges', $Paranoia))?>> Show my awards</label> -->
				
                        </td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" title="Save all changes" />
				</td>
			</tr>
			<tr class="colhead">
				<td colspan="2">
					<strong>Change password</strong>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Current password</strong></td>
				<td><input class="long" type="password" name="cur_pass" id="cur_pass" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>New password</strong></td>
				<td><input class="long" type="password" name="new_pass_1" id="new_pass_1" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>Re-type new password</strong></td>
				<td><input class="long" type="password" name="new_pass_2" id="new_pass_2" value="" /></td>
			</tr>
			<tr>
				<td class="label"><strong>Reset passkey</strong></td>
				<td>
					<input type="checkbox" name="resetpasskey" />
					<label for="ResetPasskey">Any active torrents must be downloaded again to continue leeching/seeding.</label>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="right">
					<input type="submit" value="Save Profile" title="Save all changes"/>
				</td>
			</tr>
		</table>
	</form>
</div>
<?
show_footer();
?>
