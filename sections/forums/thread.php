<?
//TODO: Normalize thread_*_info don't need to waste all that ram on things that are already in other caches
/**********|| Page to show individual threads || ********************************\

Things to expect in $_GET:
	ThreadID: ID of the forum curently being browsed
	page:	The page the user's on.
	page = 1 is the same as no page

********************************************************************************/

//---------- Things to sort out before it can start printing/generating content

include(SERVER_ROOT.'/classes/class_text.php');

$Text = new TEXT;

// Check for lame SQL injection attempts
if(!isset($_GET['threadid']) || !is_number($_GET['threadid'])) {
	if(isset($_GET['topicid']) && is_number($_GET['topicid'])) {
		$ThreadID = $_GET['topicid'];
	}
	elseif(isset($_GET['postid']) && is_number($_GET['postid'])) {
		$DB->query("SELECT TopicID FROM forums_posts WHERE ID = $_GET[postid]");
		list($ThreadID) = $DB->next_record();
		if($ThreadID) {
			header("Location: forums.php?action=viewthread&threadid=$ThreadID&postid=$_GET[postid]#post$_GET[postid]");
			die();
		} else {
			error(404);
		}
	} else {
		error(404);
	}
} else {
	$ThreadID = $_GET['threadid'];
}



if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}

//---------- Get some data to start processing

// Thread information, constant across all pages
$ThreadInfo = get_thread_info($ThreadID, true, true);
$ForumID = $ThreadInfo['ForumID'];

// Make sure they're allowed to look at the page
if(!check_forumperm($ForumID)) {
	error(403);
}

//update thread views
$DB->query("UPDATE forums_topics SET NumViews = NumViews+1 WHERE ID=$ThreadID");
//cache thread views
if ($Cache->get_value('thread_views_'.$ThreadID)===false){
    $DB->query("SELECT NumViews FROM forums_topics WHERE ID='$ThreadID'");
    list($NumViews) = $DB->next_record();
    $Cache->cache_value('thread_views_'.$ThreadID, $NumViews, 0);
} else {
    $Cache->increment('thread_views_'.$ThreadID);
}

//Post links utilize the catalogue & key params to prevent issues with custom posts per page
if($ThreadInfo['Posts'] > $PerPage) {
	if(isset($_GET['post']) && is_number($_GET['post'])) {
		$PostNum = $_GET['post'];
	} elseif(isset($_GET['postid']) && is_number($_GET['postid'])) {
		$DB->query("SELECT COUNT(ID) FROM forums_posts WHERE TopicID = $ThreadID AND ID <= $_GET[postid]");
		list($PostNum) = $DB->next_record();
	} else {
		$PostNum = 1;
	}
} else {
	$PostNum = 1;
}
list($Page,$Limit) = page_limit($PerPage, min($ThreadInfo['Posts'],$PostNum));
list($CatalogueID,$CatalogueLimit) = catalogue_limit($Page,$PerPage,THREAD_CATALOGUE);

// Cache catalogue from which the page is selected, allows block caches and future ability to specify posts per page
if(!$Catalogue = $Cache->get_value('thread_'.$ThreadID.'_catalogue_'.$CatalogueID)) {
	$DB->query("SELECT
		p.ID,
		p.AuthorID,
		p.AddedTime,
		p.Body,
		p.EditedUserID,
		p.EditedTime,
		ed.Username
		FROM forums_posts as p
		LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
		LEFT JOIN users_main AS a ON a.ID = p.AuthorID
		WHERE p.TopicID = '$ThreadID' AND p.ID != '".$ThreadInfo['StickyPostID']."'
              ORDER BY p.AddedTime
		LIMIT $CatalogueLimit");
	$Catalogue = $DB->to_array(false,MYSQLI_ASSOC);
	if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
		$Cache->cache_value('thread_'.$ThreadID.'_catalogue_'.$CatalogueID, $Catalogue, 0);
	}
}
$Thread = catalogue_select($Catalogue,$Page,$PerPage,THREAD_CATALOGUE);

if ($_GET['updatelastread'] != '0') {
	$LastPost = end($Thread);
	$LastPost = $LastPost['ID'];
	reset($Thread);

	//Handle last read // - if staff can post in locked threads we need to record last read in them or unread topics gets screwy
	//if (!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
		$DB->query("SELECT PostID From forums_last_read_topics WHERE UserID='$LoggedUser[ID]' AND TopicID='$ThreadID'");
		list($LastRead) = $DB->next_record();
		if($LastRead < $LastPost) {
			$DB->query("INSERT INTO forums_last_read_topics
				(UserID, TopicID, PostID) VALUES
				('$LoggedUser[ID]', '".$ThreadID ."', '".db_string($LastPost)."')
				ON DUPLICATE KEY UPDATE PostID='$LastPost'");
		}
	//}
}

//Handle subscriptions
if(($UserSubscriptions = $Cache->get_value('subscriptions_user_'.$LoggedUser['ID'])) === FALSE) {
	$DB->query("SELECT TopicID FROM users_subscriptions WHERE UserID = '$LoggedUser[ID]'");
	$UserSubscriptions = $DB->collect(0);
	$Cache->cache_value('subscriptions_user_'.$LoggedUser['ID'],$UserSubscriptions,0);
}

if(empty($UserSubscriptions)) {
	$UserSubscriptions = array();
}

if(in_array($ThreadID, $UserSubscriptions)) {
	$Cache->delete_value('subscriptions_user_new_'.$LoggedUser['ID']);
}

// Start printing
//show_header('Forums'.' > '.$Forums[$ForumID]['Name'].' > '.$ThreadInfo['Title'],'comments,subscriptions,bbcode,jquery');
show_header((empty($LoggedUser['ShortTitles'])?"Forums > {$Forums[$ForumID][Name]} > $ThreadInfo[Title]":$ThreadInfo['Title'] ),'comments,subscriptions,bbcode,jquery');
?>
<div class="thin">
    <? print_latest_forum_topics(); ?>
	<div class="linkbox">
		<div class="center">
			[<a href="reports.php?action=report&amp;type=thread&amp;id=<?=$ThreadID?>">Report Thread</a>]
			[<a href="#" onclick="Subscribe(<?=$ThreadID?>);return false;" id="subscribelink<?=$ThreadID?>"><?=(in_array($ThreadID, $UserSubscriptions) ? 'Unsubscribe' : 'Subscribe')?></a>]
			[<a href="#" onclick="$('#searchthread').toggle(); this.innerHTML = (this.innerHTML == '[Search this Thread]'?'[Hide Search]':'[Search this Thread]'); return false;">Search this Thread</a>]
            [<a href="forums.php?action=unread">Unread Posts</a>]
		</div>
		<div id="searchthread" class="hidden center">
			<div style="display: inline-block;">
                            <br />
				<div class="head">Search this thread:</div>
				<form action="forums.php" method="get">
					<table cellpadding="6" cellspacing="1" border="0" class="border">	
						<input type="hidden" name="action" value="search" />
						<input type="hidden" name="threadid" value="<?=$ThreadID?>" />
						<tr>
							<td><strong>Search for:</strong></td><td><input type="text" id="searchbox" name="search" size="70" /></td>
						</tr>
						<tr>
							<td><strong>Username:</strong></td><td><input type="text" id="username" name="user" size="70" /></td>
						</tr>
						<tr><td colspan="2" style="text-align: center"><input type="submit" name="submit" value="Search" /></td></tr>
					</table>
				</form>
				<br />
			</div>
		</div>
            
<?
$Pages=get_pages($Page,$ThreadInfo['Posts'],$PerPage,9);
echo $Pages;
?>
</div>
<div class="head">
    <a href="forums.php">Forums</a> &gt;
    <a href="forums.php?action=viewforum&amp;forumid=<?=$ThreadInfo['ForumID']?>"><?=$Forums[$ForumID]['Name']?></a> &gt;
    <?=display_str($ThreadInfo['Title'])?>
</div>

<?
if ($ThreadInfo['NoPoll'] == 0) {
	if (!list($Question,$Answers,$Votes,$Featured,$Closed) = $Cache->get_value('polls_'.$ThreadID)) {
		$DB->query("SELECT Question, Answers, Featured, Closed FROM forums_polls WHERE TopicID='".$ThreadID."'");
		list($Question, $Answers, $Featured, $Closed) = $DB->next_record(MYSQLI_NUM, array(1));
		$Answers = unserialize($Answers);
		$DB->query("SELECT Vote, COUNT(UserID) FROM forums_polls_votes WHERE TopicID='$ThreadID' GROUP BY Vote");
		$VoteArray = $DB->to_array(false, MYSQLI_NUM);
		
		$Votes = array();
		foreach ($VoteArray as $VoteSet) {
			list($Key,$Value) = $VoteSet; 
			$Votes[$Key] = $Value;
		}
		
		foreach(array_keys($Answers) as $i) {
			if (!isset($Votes[$i])) {
				$Votes[$i] = 0;
			}
		}
		$Cache->cache_value('polls_'.$ThreadID, array($Question,$Answers,$Votes,$Featured,$Closed), 0);
	}
	
	if (!empty($Votes)) {
		$TotalVotes = array_sum($Votes);
		$MaxVotes = max($Votes);
	} else {
		$TotalVotes = 0;
		$MaxVotes = 0;
	}
	
	$RevealVoters = in_array($ForumID, $ForumsRevealVoters);
	//Polls lose the you voted arrow thingy
	$DB->query("SELECT Vote FROM forums_polls_votes WHERE UserID='".$LoggedUser['ID']."' AND TopicID='$ThreadID'");
	list($UserResponse) = $DB->next_record();
	if (!empty($UserResponse) && $UserResponse != 0) {
		$Answers[$UserResponse] = '&raquo; '.$Answers[$UserResponse];
	} else {
		if(!empty($UserResponse) && $RevealVoters) {
			$Answers[$UserResponse] = '&raquo; '.$Answers[$UserResponse];
		}
	}

?>        
	<div class="box clear">
		<div class="colhead_dark"><strong>Poll<? if ($Closed) { echo ' [Closed]'; } ?><? if ($Featured && $Featured !== '0000-00-00 00:00:00') { echo ' [Featured]'; } ?></strong> 
                <a href="#" onclick="$('#threadpoll').toggle(); this.innerHTML=(this.innerHTML=='(Hide)'?'(View)':'(Hide)'); return false;"><?=( $ThreadInfo['IsLocked']?'(View)':'(Hide)')?></a>
            </div>
		<div class="pad<? if (/*$LastRead !== null || */$ThreadInfo['IsLocked']) { echo ' hidden'; } ?>" id="threadpoll">
			<p><strong><?=display_str($Question)?></strong></p>
<?	//if ($UserResponse !== null || $Closed || $ThreadInfo['IsLocked'] || !check_forumperm($ForumID)) { 

        $show = $UserResponse !== null || $Closed || $ThreadInfo['IsLocked'];
        
        if ($show || check_perms('forums_polls_moderate')) {
?>
            <div id="poll_votes_container">
<?                  
            if (!$show) {   ?>
                  <a href="#" onclick="$('#poll_votes').toggle(); this.innerHTML=(this.innerHTML=='(Hide Results)'?'(View Results)':'(Hide Results)'); return false;">(View Results)</a><br/>
<?          }        //<?if(!$show){echo" hidden";}    ?>
                  
                <div id="poll_votes" <?if(!$show){echo' class="hidden"';}?>>
			<ul class="poll nobullet">
<?		
		if(!$RevealVoters) {
			foreach($Answers as $i => $Answer) {
				if (!empty($Votes[$i]) && $TotalVotes > 0) {
					$Ratio = $Votes[$i]/$MaxVotes;
					$Percent = $Votes[$i]/$TotalVotes;
				} else {
					$Ratio=0;
					$Percent=0;
				}
?>
					<li><?=display_str($Answer)?> (<?=number_format($Percent*100,2)?>%)</li>
					<li class="graph">
						<span class="left_poll"></span>
						<span class="center_poll" style="width:<?=round($Ratio*750)?>px;"></span>
						<span class="right_poll"></span>
					</li>
<?			}
			if ($Votes[0] > 0) {
?>
				<li>(Blank) (<?=number_format((float)($Votes[0]/$TotalVotes*100),2)?>%)</li>
				<li class="graph">
					<span class="left_poll"></span>
					<span class="center_poll" style="width:<?=round(($Votes[0]/$MaxVotes)*750)?>px;"></span>
					<span class="right_poll"></span>
				</li>
<?			} ?>
			</ul>
			<strong>Votes:</strong> <?=number_format($TotalVotes)?><br /><br />
<?
		} else {
			//Staff forum, output voters, not percentages
			include(SERVER_ROOT.'/sections/staff/functions.php');
			$Staff = get_staff();

			$StaffNames = array();
			foreach($Staff as $Staffer) {
				$StaffNames[] = $Staffer['Username'];
			}

			$DB->query("SELECT fpv.Vote AS Vote,
						GROUP_CONCAT(um.Username SEPARATOR ', ')
						FROM users_main AS um 
							LEFT JOIN forums_polls_votes AS fpv ON um.ID = fpv.UserID
						WHERE TopicID = ".$ThreadID."
						GROUP BY fpv.Vote");
			
			$StaffVotesTmp = $DB->to_array();
			$StaffCount = count($StaffNames);

			$StaffVotes = array();
			foreach($StaffVotesTmp as $StaffVote) {
				list($Vote, $Names) = $StaffVote;
				$StaffVotes[$Vote] = $Names;
				$Names = explode(", ", $Names);
				$StaffNames = array_diff($StaffNames, $Names);
			}
?>			<ul style="list-style: none;" id="poll_options">
<?

			foreach($Answers as $i => $Answer) {
?>
				<li>
					<a href="forums.php?action=change_vote&amp;threadid=<?=$ThreadID?>&amp;auth=<?=$LoggedUser['AuthKey']?>&amp;vote=<?=(int) $i?>"><?=display_str($Answer == '' ? "Blank" : $Answer)?></a>
					 - <?=$StaffVotes[$i]?>&nbsp;(<?=number_format(((float) $Votes[$i]/$TotalVotes)*100, 2)?>%)
					 <a href="forums.php?action=delete_poll_option&amp;threadid=<?=$ThreadID?>&amp;auth=<?=$LoggedUser['AuthKey']?>&amp;vote=<?=(int) $i?>">[X]</a>
                        </li>
<?			} ?>
				<li><a href="forums.php?action=change_vote&amp;threadid=<?=$ThreadID?>&amp;auth=<?=$LoggedUser['AuthKey']?>&amp;vote=0">Blank</a> - <?=$StaffVotes[0]?>&nbsp;(<?=number_format(((float) $Votes[0]/$TotalVotes)*100, 2)?>%)</li>
			</ul>
<?
			if($ForumID == STAFF_FORUM) {
?>
			<br />
			<strong>Votes:</strong> <?=number_format($TotalVotes)?> / <?=$StaffCount ?>
			<br />
			<strong>Missing Votes:</strong> <?=implode(", ", $StaffNames)?>
			<br /><br />
<?
			}
?>
			<a href="#" onclick="AddPollOption(<?=$ThreadID?>); return false;">[+]</a>
<?
		}

?>        
                </div>
            </div>
            <br />
<?                  
        }
        if ($UserResponse == null && !$Closed && !$ThreadInfo['IsLocked'] ) { 
        //User has not voted
?>
			<div id="poll_results">
				<form id="polls">
					<input type="hidden" name="action" value="poll"/>
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="large" value="1"/>
					<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
					<ul style="list-style: none;" id="poll_options">
<?          foreach($Answers as $i => $Answer) { //for ($i = 1, $il = count($Answers); $i <= $il; $i++) { ?>
						<li>
							<input type="radio" name="vote" id="answer_<?=$i?>" value="<?=$i?>" />
							<label for="answer_<?=$i?>"><?=display_str($Answer)?></label>
						</li>
<?          } ?>
						<li>
							<br />
							<input type="radio" name="vote" id="answer_0" value="0" /> <label for="answer_0">Blank - Show the results - note: counts as a vote</label><br />
						</li>
					</ul>
<?          if($ForumID == STAFF_FORUM) { ?>
					<a href="#" onclick="AddPollOption(<?=$ThreadID?>); return false;">[+]</a>
					<br />
					<br />
<?          } ?>
					<input type="button" style="float: left;" onclick="ajax.post('index.php','polls',function(response){$('#poll_results').raw().innerHTML = response;$('#poll_votes_container').remove()});" value="Vote">
				</form>
			</div>
<? } 
 
    if(check_perms('forums_polls_moderate') && !$RevealVoters) {  
	  
        if (!$Featured || $Featured == '0000-00-00 00:00:00') { ?>
			<form action="forums.php" method="post">
				<input type="hidden" name="action" value="poll_mod"/>
				<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
				<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
				<input type="hidden" name="feature" value="1">
				<input type="submit" style="float: left;" onclick="return confirm('Are you sure you want to feature this poll?');" value="Feature" />
			</form>
	<? } ?>
			<form action="forums.php" method="post">
				<input type="hidden" name="action" value="poll_mod"/>
				<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
				<input type="hidden" name="topicid" value="<?=$ThreadID?>" />
				<input type="hidden" name="close" value="1">
				<input type="submit" style="float: left;" value="<?=(!$Closed ? 'Close' : 'Open')?>">
			</form>
<? } ?>
		</div>
	</div>
<? 
} //End Polls
  
// form for splitting posts... only include as appropriate
if(check_perms('site_moderate_forums') && $ThreadInfo['Posts'] > 1) { ?>
<form action="forums.php" method="post">
<?  } 

//Sqeeze in stickypost
if($ThreadInfo['StickyPostID']) {
    // if a sticky post is present then prepend it to the array, (it will not be in the array)
	array_unshift($Thread, $ThreadInfo['StickyPost']);
      /*  // mifune: I think this fixes sticky posts... if a stickypostID is present it will NOT be in the post list,
       *  // so afaics it should ALWAYS be prepended to the post list - no point in testing if its the first post...
       * //   and why stick it on the end??
	if($ThreadInfo['StickyPostID'] != $Thread[0]['ID']) {
		array_unshift($Thread, $ThreadInfo['StickyPost']);
	}
	if($ThreadInfo['StickyPostID'] != $Thread[count($Thread)-1]['ID']) {
		$Thread[] = $ThreadInfo['StickyPost'];
	} */
}
foreach($Thread as $Key => $Post){
	list($PostID, $AuthorID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername) = array_values($Post);
	list($AuthorID, $Username, $PermissionID, $Paranoia, $Donor, $Warned, $Avatar, $Enabled, $UserTitle,,,$Signature,,$GroupPermissionID) = array_values(user_info($AuthorID));
	$AuthorPermissions = get_permissions($PermissionID);
      list($ClassLevel,$PermissionValues,$MaxSigLength,$MaxAvatarWidth,$MaxAvatarHeight)=array_values($AuthorPermissions);
      // we need to get custom permissions for this author
      $PermissionValues = get_permissions_for_user($AuthorID, false, $AuthorPermissions);
	// Image proxy CTs
	if(check_perms('site_proxy_images') && !empty($UserTitle)) {
		$UserTitle = preg_replace_callback('~src=("?)(http.+?)(["\s>])~', function($Matches) {
						 return 'src='.$Matches[1].'http'.($SSL?'s':'').'://'.SITE_URL.'/image.php?c=1&amp;i='.urlencode($Matches[2]).$Matches[3];
					 }, $UserTitle);
	}
?>
<table class="forum_post box vertical_margin<? if (((!$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) && $PostID>$LastRead && strtotime($AddedTime)>$LoggedUser['CatchupTime']) || (isset($RequestKey) && $Key==$RequestKey)) { echo ' forum_unread'; } if($HeavyInfo['DisableAvatars']) { echo ' noavatar'; } ?>" id="post<?=$PostID?>">
	<tr class="smallhead">
		<td colspan="2">
			<span style="float:left;"><a class="post_id" href='forums.php?action=viewthread&amp;threadid=<?=$ThreadID?>&amp;postid=<?=$PostID?>#post<?=$PostID?>'>#<?=$PostID?></a>
				<?=format_username($AuthorID, $Username, $Donor, $Warned, $Enabled, $PermissionID, $UserTitle, true, $GroupPermissionID, true)?>
                        <?=time_diff($AddedTime,2)?> 
<? if(!$ThreadInfo['IsLocked'] || check_perms('site_moderate_forums')){ ?> 
				- <a href="#quickpost" onclick="Quote('<?=$PostID?>','f<?=$ThreadID?>','<?=$Username?>');">[Quote]</a> 
<? }
if (((!$ThreadInfo['IsLocked'] && check_forumperm($ForumID, 'Write')) && can_edit_comment($AuthorID, $EditedUserID, $AddedTime, $EditedTime)) || check_perms('site_moderate_forums')) { ?>
				- <a href="#post<?=$PostID?>" onclick="Edit_Form('<?=$PostID?>','<?=$Key?>');">[Edit]</a> 
<? }
if($ForumID != TRASH_FORUM_ID && check_perms('site_moderate_forums') && $ThreadInfo['Posts'] > 1) { ?> 
				- <a href="#post<?=$PostID?>" onclick="Trash('<?=$ThreadID?>','<?=$PostID?>');" title="moves this post to the trash forum">[Trash]</a> 
<? }
if(check_perms('site_admin_forums') && $ThreadInfo['Posts'] > 1) { ?> 
				- <a href="#post<?=$PostID?>" onclick="Delete('<?=$PostID?>');" title="permenantly delete this post">[Delete]</a> 
<? }
if($PostID == $ThreadInfo['StickyPostID']) { ?>
				<strong><span class="sticky_post">[Sticky]</span></strong>
<?	if(check_perms('site_moderate_forums')) { ?>
				- <a href="forums.php?action=sticky_post&amp;threadid=<?=$ThreadID?>&amp;postid=<?=$PostID?>&amp;remove=true&amp;auth=<?=$LoggedUser['AuthKey']?>" title="unsticky this post">[X]</a>
<?	}
} else {
	if(check_perms('site_moderate_forums')) { ?>
				- <a href="forums.php?action=sticky_post&amp;threadid=<?=$ThreadID?>&amp;postid=<?=$PostID?>&amp;auth=<?=$LoggedUser['AuthKey']?>" title="make this post sticky (appears at the top of every page)">[&#x21d5;]</a>
<? 	}
}
?>
			</span>
			<span id="bar<?=$PostID?>" style="float:right;">
<?      if(check_perms('site_moderate_forums') && $ThreadInfo['Posts'] > 1) { ?>
                        <label class="split hidden">split</label>
                        <input class="split hidden" type="checkbox" id="split_<?=$PostID?>" name="splitids[]" value="<?=$PostID?>" />
				&nbsp;&nbsp;
<?      }     ?>
				<a href="reports.php?action=report&amp;type=post&amp;id=<?=$PostID?>">[Report]</a>
				&nbsp;
				<a href="#">&uarr;</a>
			</span>
		</td>
	</tr>
	<tr>
<? if(empty($HeavyInfo['DisableAvatars'])) {   ?>
          <td class="avatar" valign="top" <?=(empty($HeavyInfo['DisableSignatures']) && ($MaxSigLength > 0) && !empty($Signature)) ? 'rowspan="2"' : ''?>>
	<? if ($Avatar) { ?>
			<img src="<?=$Avatar?>" class="avatar" style="<?=get_avatar_css($MaxAvatarWidth, $MaxAvatarHeight)?>" alt="<?=$Username ?>'s avatar" />
	<? } else { ?>
			<img src="<?=STATIC_SERVER?>common/avatars/default.png"  class="avatar" style="<?=get_avatar_css(100, 120)?>" alt="Default avatar" />
	<? }  
                  
        $UserBadges = get_user_badges($AuthorID);
        if( !empty($UserBadges) ) {  ?>
               <div class="badges">
<?                  print_badges_array($UserBadges, $AuthorID);  ?>
               </div>
<?      }      ?>
           </td>
<? }
$AllowTags= isset($PermissionValues['site_advanced_tags']) &&  $PermissionValues['site_advanced_tags'];
?>
		<td class="postbody" valign="top"<? if(!empty($HeavyInfo['DisableAvatars'])) { echo ' colspan="2"'; } ?>>
                    <div id="content<?=$PostID?>" class="post_container">
                      <div class="post_content"><?=$Text->full_format($Body, $AllowTags) ?> </div>
       
                      
<? if($EditedUserID){ ?>  
                        <div class="post_footer">
<?	if(check_perms('site_moderate_forums')) { ?>
				<a href="#content<?=$PostID?>" onclick="LoadEdit('forums', <?=$PostID?>, 1); return false;">&laquo;</a> 
<? 	} ?>
                            <span class="editedby">Last edited by
                            <?=format_username($EditedUserID, $EditedUsername) ?> <?=time_diff($EditedTime,2,true,true)?>
                            </span>
                        </div>
        <? }   ?>  
                    </div>
		</td>
	</tr>
<? 
      if( empty($HeavyInfo['DisableSignatures']) && ($MaxSigLength > 0) && !empty($Signature) ) { //post_footer
                        
            echo '
      <tr>
            <td class="sig"><div id="sig" style="max-height: '.SIG_MAX_HEIGHT. 'px"><div>' . $Text->full_format($Signature, $AllowTags) . '</div></div></td>
      </tr>';
           }
?>
</table>
        
<?	} ?>
    
<div class="breadcrumbs">
	<a href="forums.php">Forums</a> &gt;
	<a href="forums.php?action=viewforum&amp;forumid=<?=$ThreadInfo['ForumID']?>"><?=$Forums[$ForumID]['Name']?></a> &gt;
	<?=display_str($ThreadInfo['Title'])?>
</div>
    
<div id="splittool" class="linkbox">
	<?=$Pages?>
</div>
<? 
    if(check_perms('site_moderate_forums') && $ThreadInfo['Posts'] > 1) { ?> 
          
	<div class="head split hidden">Split thread (select posts to be split) <span style="float:right"><a href="#splittool" onclick="$('.split').toggle();">Show/Hide split tool</a></span></div>
	<table cellpadding="6" cellspacing="1" border="0" width="100%" class="border split hidden">
                
		<input type="hidden" name="action" value="mod_thread" />
		<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		<input type="hidden" name="threadid" value="<?=$ThreadID?>" />
		<input type="hidden" name="page" value="<?=$Page?>" />
			<tr>
				<td class="label" title="Action to carry out on split">Split Type: </td>
				<td> 
                            <input type="hidden" name="split" value="1"/>
                            <input type="radio" name="splitoption" id="split_new" value="newsplit" onchange="SetSplitInterface()" checked="checked" /> into <em>new</em> thread &nbsp;&nbsp;&nbsp;
                            <input type="radio" name="splitoption" id="split_merge" value="mergesplit"  onchange="SetSplitInterface()" />
                            <label for="splitintothreadid">into <em>existing</em> thread with id:</label>
                            <input type="text" name="splitintothreadid" id="split_threadid" value="" disabled="disabled"/>&nbsp;&nbsp;&nbsp;&nbsp; 
                            <input type="radio" name="splitoption" id="split_trash" value="trashsplit"  onchange="SetSplitInterface()" /> Trash selected &nbsp;&nbsp;&nbsp;
<?  if(check_perms('site_admin_forums') && $ThreadInfo['Posts'] > 1) { ?> 
                            <input type="radio" name="splitoption" id="split_delete" value="deletesplit"  onchange="SetSplitInterface()" /> Delete selected &nbsp;&nbsp;&nbsp;
<?  } ?> 
                </td>
			</tr>
			<tr>
				<td class="label">New Title* </td>
				<td>
					<input type="text" name="title" id="split_title" class="long" value="<?=display_str($ThreadInfo['Title'])?>" />
				</td>
			</tr>
			<tr>
				<td class="label">New forum* </td>
				<td> 
                            <?= print_forums_select($Forums, $ForumCats, $ThreadInfo['ForumID'], 'split_forum') ?>
				</td>
			</tr>
			<tr>
				<td class="label">Comment**</td>
				<td>
					<input type="text" name="comment" id="split_comment" class="long" value="" disabled="disabled" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center">
                    <span style="float:left">*only used if splitting into new &nbsp;&nbsp; **only used if trashing</span>
					<input type="submit" value="Split selected posts" />
				</td>
			</tr>
      </table>
</form>
<?  } ?>
    
<?
if(!$ThreadInfo['IsLocked'] || check_perms('site_moderate_forums')) {
	if(check_forumperm($ForumID, 'Write') && !$LoggedUser['DisablePosting']) {
	//TODO: Preview, come up with a standard, make it look like post or just a block of formatted bbcode, but decide and write some proper html
?>
			<div class="messagecontainer" id="container"><div id="message" class="hidden center messagebar"></div></div>
				<table id="quickreplypreview" class="forum_post box vertical_margin hidden" style="text-align:left;">
					<tr class="smallhead">
						<td colspan="2">
							<span style="float:left;"><a href='#quickreplypreview'>#XXXXXX</a>
								<?=format_username($LoggedUser['ID'], $LoggedUser['Username'], $LoggedUser['Donor'], $LoggedUser['Warned'], $LoggedUser['Enabled'], $LoggedUser['PermissionID'], $LoggedUser['Title'], true)?> 
							Just now
							</span>
							<span id="barpreview" style="float:right;">
								<a href="#quickreplypreview">[Report]</a>
								&nbsp;
								<a href="#">&uarr;</a>
							</span>
						</td>
					</tr>
					<tr>
                        <? if(empty($HeavyInfo['DisableAvatars'])) { ?>
                                    <td class="avatar" valign="top">
                              <? if (!empty($LoggedUser['Avatar'])) {  ?>
                                            <img src="<?=$LoggedUser['Avatar']?>" class="avatar" style="<?=get_avatar_css($LoggedUser['MaxAvatarWidth'], $LoggedUser['MaxAvatarHeight'])?>" alt="<?=$LoggedUser['Username']?>'s avatar" />
                               <? } else { ?>
                                          <img src="<?=STATIC_SERVER?>common/avatars/default.png" class="avatar" style="<?=get_avatar_css(100, 120)?>" alt="Default avatar" />
                              <? } ?>
                                    </td>
                        <? } ?>  
						<td class="body" valign="top">
							<div id="contentpreview" style="text-align:left;"></div>
						</td>
					</tr>
				</table>
                  <div class="head">Post reply</div>
			<div class="box pad shadow">
				<form id="quickpostform" action="" method="post" onsubmit="return Validate_Form('message','quickpost')" style="display: block; text-align: center;">
					<input type="hidden" name="action" value="reply" />
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="thread" value="<?=$ThreadID?>" />

					<div id="quickreplytext">
                            <?  $Text->display_bbcode_assistant("quickpost", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); ?>
						<textarea id="quickpost" class="long" tabindex="1" onkeyup="resize('quickpost');" name="body" rows="8"></textarea> <br />
					</div>
					<div>
<? if(!in_array($ThreadID, $UserSubscriptions)) { ?>
						<input id="subscribebox" type="checkbox" name="subscribe"<?=!empty($HeavyInfo['AutoSubscribe'])?' checked="checked"':''?> tabindex="2" />
						<label for="subscribebox">Subscribe</label>
<?
}
	if($ThreadInfo['LastPostAuthorID']==$LoggedUser['ID'] && (check_perms('site_forums_double_post') || in_array($ForumID, $ForumsDoublePost))) {
?>
						<input id="mergebox" type="checkbox" name="merge" checked="checked" tabindex="2" />
						<label for="mergebox">Merge</label>
<? } ?>
						<input id="post_preview" type="button" value="Preview" tabindex="1" onclick="if(this.preview){Quick_Edit();}else{Quick_Preview();}" />
						<input type="submit" value="Post reply" tabindex="1" />
					</div>
				</form>
			</div>
<?
	}
}
if(check_perms('site_moderate_forums')) {
?>
	<br />
	<div class="head">Edit thread</div>
	<form action="forums.php" method="post">
		 
		<input type="hidden" name="action" value="mod_thread" />
		<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		<input type="hidden" name="threadid" value="<?=$ThreadID?>" />
		<input type="hidden" name="page" value="<?=$Page?>" />
		 
		<table cellpadding="6" cellspacing="1" border="0" width="100%" class="border">
			<tr>
				<td class="label">Sticky</td>
				<td>
					<input type="checkbox" name="sticky"<? if($ThreadInfo['IsSticky']) { echo ' checked="checked"'; } ?> tabindex="2" />
				</td>
			</tr>
			<tr>
				<td class="label">Locked</td>
				<td>
					<input type="checkbox" name="locked"<? if($ThreadInfo['IsLocked']) { echo ' checked="checked"'; } ?> />
				</td>
			</tr>
			<tr>
				<td class="label">Title</td>
				<td>
					<input type="text" name="title" class="long" value="<?=display_str($ThreadInfo['Title'])?>" />
				</td>
			</tr>
			<tr>
				<td class="label">Move thread</td>
				<td> 
                            <?= print_forums_select($Forums, $ForumCats, $ThreadInfo['ForumID']) ?>
				</td>
			</tr>
			<tr>
				<td class="label">Merge thread</td>
				<td>
                            <input type="checkbox" name="merge" />&nbsp;&nbsp;&nbsp;&nbsp;
                            <label for="mergethreadid">id of thread to merge <em>into</em></label>
                            <input type="text" name="mergethreadid" value="" />
				</td>
			</tr>
<?  if($ThreadInfo['Posts'] > 1) { ?> 
			<tr>
				<td class="label">Split thread</td>
				<td>
                            <a href="#splittool" onclick="$('.split').toggle();">Show/Hide split tool</a>
				</td>
			</tr>
<?  }
    if($ForumID != TRASH_FORUM_ID ) { ?>
			<tr>
				<td class="label">Trash thread</td>
				<td>
					<input type="checkbox" name="trash" /> &nbsp; Comment: <input type="text" name="comment" class="medium" value="" />
				</td>
			</tr>
<?  }
    if(check_perms('site_admin_forums')) { ?>
                  
			<tr>
				<td class="label">Delete thread</td>
				<td>
					<input type="checkbox" name="delete" />
				</td>
			</tr>
<?  } ?>
			<tr>
				<td colspan="2" class="center">
					<input type="submit" value="Edit thread" />
				</td>
			</tr>

		</table>
	</form>
<?
} // If user is moderator
?>
</div>
<? show_footer();
