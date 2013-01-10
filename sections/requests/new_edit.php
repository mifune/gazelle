<?

/*
 * Yeah, that's right, edit and new are the same place again.
 * It makes the page uglier to read but ultimately better as the alternative means
 * maintaining 2 copies of almost identical files. 
 */

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

if(!check_perms('site_submit_requests')) error(403);

$NewRequest = ($_GET['action'] == "new" ? true : false);

if(!$NewRequest) {
	$RequestID = $_GET['id'];
	if(!is_number($RequestID)) {
		error(404);
	}
}


if($NewRequest && ($LoggedUser['BytesUploaded'] < 250*1024*1024 || !check_perms('site_submit_requests'))) {
	error('You do not have enough uploaded to make a request.');
}

if(!$NewRequest) {
	if(empty($ReturnEdit)) {
		
		$Request = get_requests(array($RequestID));
		$Request = $Request['matches'][$RequestID];
		if(empty($Request)) {
			error(404);
		}
		
		list($RequestID, $RequestorID, $RequestorName, $TimeAdded, $LastVote, $CategoryID, $Title, $Image, $Description, 
		     $FillerID, $FillerName, $TorrentID, $TimeFilled, $GroupID) = $Request;
		$VoteArray = get_votes_array($RequestID);
		$VoteCount = count($VoteArray['Voters']);
				
		$IsFilled = !empty($TorrentID);
		$CategoryName = $NewCategories[$CategoryID]['name'];
		$ProjectCanEdit = (check_perms('project_team') && !$IsFilled && (($CategoryID == 0)));
		$CanEdit = ((!$IsFilled && $LoggedUser['ID'] == $RequestorID && $VoteCount < 2) || $ProjectCanEdit || check_perms('site_moderate_requests'));
		
		if(!$CanEdit) {
			error(403);
		}
				
		$Tags = implode(" ", $Request['Tags']);
	}
}

if($NewRequest && !empty($_GET['groupid']) && is_number($_GET['groupid'])) {
	$DB->query("SELECT 
                            tg.Name, 					
                            tg.Image,
                            GROUP_CONCAT(t.Name SEPARATOR ', '),
                    FROM torrents_group AS tg 
                            JOIN torrents_tags AS tt ON tt.GroupID=tg.ID
                            JOIN tags AS t ON t.ID=tt.TagID
                    WHERE tg.ID = ".$_GET['groupid']);
	if(list($Title, $Image, $Tags) = $DB->next_record()) {
		$GroupID = trim($_REQUEST['groupid']);
	}
}

show_header(($NewRequest ? "Create a request" : "Edit a request"), 'requests,bbcode');
?>
<div class="thin">
	<h2><?=($NewRequest ? "Create a request" : "Edit a request")?></h2>
	
	<div class="linkbox">
            <a href="requests.php">[Search requests]</a> 
            <a href="requests.php?type=created">[My requests]</a>
<?	 if(check_perms('site_vote')){?> 
            <a href="requests.php?type=voted">[Requests I've voted on]</a>
<?		}  ?>
 
	</div>
      <div class="head"><?=($NewRequest ? "Create New Request" : "Edit Request")?></div>
	<div class="box pad">
		<form action="" method="post" id="request_form" onsubmit="Calculate();">
<? if(!$NewRequest) { ?>
				<input type="hidden" name="requestid" value="<?=$RequestID?>" /> 
<? } ?>
				<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
				<input type="hidden" name="action" value="<?=$NewRequest ? 'takenew' : 'takeedit'?>" />
			
			<table>
				<tr>
					<td colspan="2" class="center">Please make sure your request follows <a href="articles.php?topic=requests">the request rules!</a></td>
				</tr>
<?	if($NewRequest || $CanEdit) { ?>
                <tr class="pad">
                    <td colspan="2" class="center">
                        <strong class="important_text">NOTE: Requests automatically expire after 90 days. At this time if the bounty has not been filled all outstanding bounties are returned to those who placed them</strong>
                    </td>
                </tr>
				<tr>
					<td class="label">
						Category
					</td>
					<td>
						<select id="categories" name="category">
<? foreach($NewCategories as $Cat){ $Cat = display_array($Cat); ?>
							<option value='<?=$Cat['id']?>' <?=(!empty($CategoryName) && ($CategoryName ==  $Cat['name']) ? 'selected="selected"' : '')?>><?=$Cat['name']?></option>
<?		} ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label">Title</td>
					<td>
						<input type="text" name="title" class="long" value="<?=(!empty($Title) ? display_str($Title) : '')?>" />
					</td>
				</tr>
<?	//} ?>
<?	//if($NewRequest || $CanEdit) { ?>
				<tr id="image_tr">
                            <td class="label">Cover Image</td>
                            <td>    <strong>Enter the full url for your image.</strong><br/>
                                        Note: Do not add a thumbnail image as cover, rather leave this field blank if you don't have a good cover image or an image of the actor(s).
                                 <input type="text" id="image" class="long" name="image" value="<?=(!empty($Image) ? $Image : '')?>" />
                            </td>
				</tr>
<?	} ?>
				<tr>
					<td class="label">Tags</td>
					<td>
<?
	$GenreTags = $Cache->get_value('genre_tags');
	if(!$GenreTags) {
		$DB->query('SELECT Name FROM tags WHERE TagType=\'genre\' ORDER BY Name');
		$GenreTags =  $DB->collect('Name');
		$Cache->cache_value('genre_tags', $GenreTags, 3600*6);
	}
?>
						<select id="genre_tags" name="genre_tags" onchange="add_tag();return false;" >
							<option>---</option>
<?	foreach(display_array($GenreTags) as $Genre){ ?>
							<option value="<?=$Genre ?>"><?=$Genre ?></option>
<?	} ?>
						</select>
						<input type="text" id="tags" name="tags" class="medium"  value="<?=(!empty($Tags) ? display_str($Tags) : '')?>" />
						<br />
					<? 
                                      $taginfo = get_article('tagrulesinline');
                                      if($taginfo) echo $Text->full_format($taginfo, true); 
                              ?>
					</td>
				</tr>
				<tr>
					<td class="label">Description</td>
					<td>  <div id="preview" class="box pad hidden"></div>
                                    <div  id="editor">
                                         <? $Text->display_bbcode_assistant("quickcomment", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); ?>
                                        <textarea  id="quickcomment" name="description" class="long" rows="7"><?=(!empty($Description) ? $Description : '')?></textarea> 
                                    </div>
                                    <input type="button" id="previewbtn" value="Preview" style="margin-right: 40px;" onclick="Preview_Request();" />
                              </td>
				</tr>

<?	if($NewRequest) { ?>
				<tr id="voting">
					<td class="label" id="bounty">Bounty</td>
					<td>
						<input type="text" id="amount_box" size="8" value="<?=(!empty($Bounty) ? $Bounty : '100')?>" onchange="Calculate();" />
						<select id="unit" name="unit" onchange="Calculate();">
							<option value='mb'<?=(!empty($_POST['unit']) && $_POST['unit'] == 'mb' ? ' selected="selected"' : '') ?>>MB</option>
							<option value='gb'<?=(!empty($_POST['unit']) && $_POST['unit'] == 'gb' ? ' selected="selected"' : '') ?>>GB</option>
                                                        <option value='tb'<?=(!empty($_POST['unit']) && $_POST['unit'] == 'tb' ? ' selected="selected"' : '') ?>>TB</option>
						</select>
						<input type="button" value="Preview" onclick="Calculate();"/>
						<strong id="inform">100MB will immediately be removed from your upload total.</strong>
					</td>
				</tr>
				<tr>
					<td class="label">Post request information</td>
					<td>
						<input type="hidden" id="amount" name="amount" value="<?=(!empty($Bounty) ? $Bounty : '100')?>" />
						<input type="hidden" id="current_uploaded" value="<?=$LoggedUser['BytesUploaded']?>" />
						<input type="hidden" id="current_downloaded" value="<?=$LoggedUser['BytesDownloaded']?>" />
						If you add the entered <strong><span id="new_bounty">100.00 MB</span></strong> of bounty, your new stats will be: <br/>
						Uploaded: <span id="new_uploaded"><?=get_size($LoggedUser['BytesUploaded'])?></span>
						Ratio: <span id="new_ratio"><?=ratio($LoggedUser['BytesUploaded'],$LoggedUser['BytesDownloaded'])?></span>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<input type="submit" id="button" value="Create request" />
					</td>
				</tr>
<?	} else { ?>		
				<tr>
					<td colspan="2" class="center">
						<input type="submit" id="button" value="Edit request" />
					</td>
				</tr>
<?	} ?>
			</table>
		</form>
	</div>
</div>
<?
show_footer(); 
?>
