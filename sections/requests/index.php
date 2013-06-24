<?
enforce_login();
include(SERVER_ROOT.'/sections/requests/functions.php');

// Minimum and default amount of upload to remove from the user when they vote.
// Also change in static/functions/requests.js
$MinimumVote = 20*1024*1024;

if(!empty($LoggedUser['DisableRequests'])) {
	error('Your request privileges have been removed.');
}

//if(!check_perms('users_mod')) error("The requests section is closed while we clean up a mess created by a cheater.<br/>We apologise for the inconvenience, the requests section will reopen asap.");

if(!isset($_REQUEST['action'])) {
	include(SERVER_ROOT.'/sections/requests/requests.php');
} else {
	switch($_REQUEST['action']){
		case 'new':
		case 'edit':
			include(SERVER_ROOT.'/sections/requests/new_edit.php');
			break;
		case 'takevote':
			include(SERVER_ROOT.'/sections/requests/takevote.php');
			break;
		case 'takefill':
			include(SERVER_ROOT.'/sections/requests/takefill.php');
			break;
		case 'takenew':
		case 'takeedit':
			include(SERVER_ROOT.'/sections/requests/takenew_edit.php');
			break;
		case 'delete':
		case 'unfill':
			include(SERVER_ROOT.'/sections/requests/interim.php');
			break;
		case 'takeunfill':
			include(SERVER_ROOT.'/sections/requests/takeunfill.php');
			break;
		case 'takedelete':
			include(SERVER_ROOT.'/sections/requests/takedelete.php');
			break;
		case 'view':
		case 'viewrequest':
			include(SERVER_ROOT.'/sections/requests/request.php');
			break;
		case 'reply':
			authorize();
			enforce_login();
                  
			if (!isset($_POST['requestid']) || !is_number($_POST['requestid'])) { 
				error(0);
			}
                  if(empty($_POST['body'])) {
                        error('You cannot post a comment with no content.');
                  }
			if($LoggedUser['DisablePosting']) {
				error('Your posting rights have been removed.');
			}
			
			$RequestID = $_POST['requestid'];
			if(!$RequestID) { error(404); }
            
            flood_check('requests_comments');
            
			$DB->query("SELECT CEIL((SELECT COUNT(ID)+1 FROM requests_comments AS rc WHERE rc.RequestID='".$RequestID."')/".TORRENT_COMMENTS_PER_PAGE.") AS Pages");
			list($Pages) = $DB->next_record();
		
			$DB->query("INSERT INTO requests_comments (RequestID,AuthorID,AddedTime,Body) VALUES (
				'".$RequestID."', '".db_string($LoggedUser['ID'])."','".sqltime()."','".db_string($_POST['body'])."')");
			$PostID=$DB->inserted_id();
		
			$CatalogueID = floor((TORRENT_COMMENTS_PER_PAGE*$Pages-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			$Cache->begin_transaction('request_comments_'.$RequestID.'_catalogue_'.$CatalogueID);
			$Post = array(
				'ID'=>$PostID,
				'AuthorID'=>$LoggedUser['ID'],
				'AddedTime'=>sqltime(),
				'Body'=>$_POST['body'],
				'EditedUserID'=>0,
				'EditedTime'=>'0000-00-00 00:00:00',
				'Username'=>''
				);
			$Cache->insert('', $Post);
			$Cache->commit_transaction(0);
			$Cache->increment('request_comments_'.$RequestID);
			
			header('Location: requests.php?action=view&id='.$RequestID.'&page='.$Pages."#post$PostID");
            break;
		
		case 'get_post':
			enforce_login();
			if (!$_GET['post'] || !is_number($_GET['post'])) { error(0); }
                  $PostID = (int)$_GET['post'];
			$DB->query("SELECT Body FROM requests_comments WHERE ID='$PostID'");
			list($Body) = $DB->next_record(MYSQLI_NUM);
			
                  if (isset($_REQUEST['body']) && $_REQUEST['body']==1){
                  	echo trim($Body); 
                  } else {
                        include(SERVER_ROOT.'/classes/class_text.php');
                   
                        $Text = new TEXT;

                        $Text->display_bbcode_assistant("editbox$PostID", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); 

?>					
        <textarea id="editbox<?=$PostID?>" class="long" onkeyup="resize('editbox<?=$PostID?>');" name="body" rows="10"><?=display_str($Body)?></textarea>

<?
                  }
                  break;
		case 'takeedit_comment':
			enforce_login();
			authorize();

			include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
			$Text = new TEXT;
		
			// Quick SQL injection check
			if(!$_POST['post'] || !is_number($_POST['post'])) { error(0); }
			
			// Mainly
			$DB->query("SELECT
				rc.Body,
				rc.AuthorID,
				rc.RequestID,
				rc.AddedTime,
				rc.EditedTime
				FROM requests_comments AS rc
				WHERE rc.ID='".db_string($_POST['post'])."'");
			if ($DB->record_count()==0) { error(404); }
			list($OldBody, $AuthorID,$RequestID,$AddedTime,$EditedTime)=$DB->next_record();
			
			$DB->query("SELECT ceil(COUNT(ID) / ".POSTS_PER_PAGE.") AS Page FROM requests_comments WHERE RequestID = $RequestID AND ID <= $_POST[post]");
			list($Page) = $DB->next_record();
			
			//if ($LoggedUser['ID']!=$AuthorID && !check_perms('site_moderate_forums')) { error(404); }
			//if ($DB->record_count()==0) { error(404); }
            if (!check_perms('site_moderate_forums')){ 
                if ($LoggedUser['ID'] != $AuthorID){
                    error(403,true);
                } else if (!check_perms ('site_edit_own_posts') 
                        && time_ago($AddedTime)>(USER_EDIT_POST_TIME+600)  && time_ago($EditedTime)>(USER_EDIT_POST_TIME+300) ) { // give them an extra 15 mins in the backend because we are nice
                    error("Sorry - you only have ". date('i\m s\s', USER_EDIT_POST_TIME). "  to edit your comment before it is automatically locked." ,true);
                } 
            }
		
			// Perform the update
			$DB->query("UPDATE requests_comments SET
				Body = '".db_string($_POST['body'])."',
				EditedUserID = '".db_string($LoggedUser['ID'])."',
				EditedTime = '".sqltime()."'
				WHERE ID='".db_string($_POST['post'])."'");
		
			// Update the cache
			$CatalogueID = floor((TORRENT_COMMENTS_PER_PAGE*$Page-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			$Cache->begin_transaction('request_comments_'.$RequestID.'_catalogue_'.$CatalogueID);
			
			$Cache->update_row($_POST['key'], array(
				'ID'=>$_POST['post'],
				'AuthorID'=>$AuthorID,
				'AddedTime'=>$AddedTime,
				'Body'=>$_POST['body'],
				'EditedUserID'=>db_string($LoggedUser['ID']),
				'EditedTime'=>sqltime(),
				'Username'=>$LoggedUser['Username']
			));
			$Cache->commit_transaction(0);
			
			$DB->query("INSERT INTO comments_edits (Page, PostID, EditUser, EditTime, Body)
								VALUES ('requests', ".db_string($_POST['post']).", ".db_string($LoggedUser['ID']).", '".sqltime()."', '".db_string($OldBody)."')");
			
			// This gets sent to the browser, which echoes it in place of the old body
			//echo '<div class="post_content">' .$Text->full_format($_POST['body'], get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])). '</div>';
?>
<div class="post_content">
    <?=$Text->full_format($_POST['body'], get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions']));?>
</div>
<div class="post_footer">
    <span class="editedby">Last edited by <a href="user.php?id=<?=$LoggedUser['ID']?>"><?=$LoggedUser['Username']?></a> just now</span>
</div>        
<?
                  
                  break;
		
		case 'delete_comment':
			enforce_login();
			authorize();
		
			// Quick SQL injection check
			if (!$_GET['postid'] || !is_number($_GET['postid'])) { error(0); }
		
			// Make sure they are moderators
			if (!check_perms('site_moderate_forums')) { error(403); }
		
			// Get topicid, forumid, number of pages
			$DB->query("SELECT DISTINCT
				RequestID,
				CEIL((SELECT COUNT(rc1.ID) FROM requests_comments AS rc1 WHERE rc1.RequestID=rc.RequestID)/".TORRENT_COMMENTS_PER_PAGE.") AS Pages,
				CEIL((SELECT COUNT(rc2.ID) FROM requests_comments AS rc2 WHERE rc2.ID<'".db_string($_GET['postid'])."')/".TORRENT_COMMENTS_PER_PAGE.") AS Page
				FROM requests_comments AS rc
				WHERE rc.RequestID=(SELECT RequestID FROM requests_comments WHERE ID='".db_string($_GET['postid'])."')");
			list($RequestID,$Pages,$Page)=$DB->next_record();
		
			// $Pages = number of pages in the thread
			// $Page = which page the post is on
			// These are set for cache clearing.
		
			$DB->query("DELETE FROM requests_comments WHERE ID='".db_string($_GET['postid'])."'");
		
			//We need to clear all subsequential catalogues as they've all been bumped with the absence of this post
			$ThisCatalogue = floor((TORRENT_COMMENTS_PER_PAGE*$Page-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			$LastCatalogue = floor((TORRENT_COMMENTS_PER_PAGE*$Pages-TORRENT_COMMENTS_PER_PAGE)/THREAD_CATALOGUE);
			for($i=$ThisCatalogue;$i<=$LastCatalogue;$i++) {
				$Cache->delete('request_comments_'.$RequestID.'_catalogue_'.$i);
			}
			
			// Delete thread info cache (eg. number of pages)
			$Cache->delete('request_comments_'.$GroupID);
		break;
		
		default:
			error(0);
	}
}
?>
