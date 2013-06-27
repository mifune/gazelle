<?
authorize();

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

// Quick SQL injection check
if(!$_POST['post'] || !is_number($_POST['post'])) {
	error(404,true);
}
// End injection check

if(empty($_POST['body'])) {
	error('You cannot post a comment with no content.',true);
}

$Text->validate_bbcode($_POST['body'],  get_permissions_advtags($LoggedUser['ID']));
            
// Variables for database input
$UserID = $LoggedUser['ID'];
$Body = db_string(urldecode($_POST['body']));
$PostID = $_POST['post'];

// Mainly 
$DB->query("SELECT cc.Body, 
                   cc.UserID, 
                   cc.CollageID,
                   cc.Time, 
                   (SELECT COUNT(ID) FROM collages_comments WHERE ID <= ".$PostID." AND collages_comments.CollageID = cc.CollageID) 
            FROM collages_comments AS cc 
           WHERE cc.ID='$PostID'");
if($DB->record_count()==0) { error(404,true); }
list($OldBody, $AuthorID, $CollageID, $AddedTime, $PostNum) = $DB->next_record();

// Make sure they aren't trying to edit posts they shouldn't
// We use die() here instead of error() because whatever we spit out is displayed to the user in the box where his forum post is
//if($UserID!=$AuthorID && !check_perms('site_moderate_forums')) {
//	die('Permission denied');
//}    
if (!check_perms('site_moderate_forums')){ 
    if ($LoggedUser['ID'] != $AuthorID){
        error(403,true);
    } else if (!check_perms ('site_edit_own_posts') && time_ago($AddedTime)>(USER_EDIT_POST_TIME+600)  ) { // give them an extra 15 mins in the backend because we are nice
        error("Sorry - you only have ". date('i\m s\s', USER_EDIT_POST_TIME). "  to edit your comment before it is automatically locked." ,true);
    } 
}

// Perform the update
$DB->query("UPDATE collages_comments SET
		Body = '$Body'
		WHERE ID='$PostID'");

$Cache->delete_value('collage_'.$CollageID);


$PageNum = ceil($PostNum / TORRENT_COMMENTS_PER_PAGE);
$CatalogueID = floor((POSTS_PER_PAGE*$PageNum-POSTS_PER_PAGE)/THREAD_CATALOGUE);
$Cache->delete_value('collage_'.$CollageID.'_catalogue_'.$CatalogueID);

$DB->query("INSERT INTO comments_edits (Page, PostID, EditUser, EditTime, Body)
								VALUES ('collages', ".$PostID.", ".$UserID.", '".sqltime()."', '".db_string($OldBody)."')");

// This gets sent to the browser, which echoes it in place of the old body
//echo $Text->full_format($_POST['body']);

?>
<div class="post_content">
    <?=$Text->full_format($_POST['body'], isset($PermissionsInfo['site_advanced_tags']) &&  $PermissionsInfo['site_advanced_tags']);?>
</div>
<div class="post_footer">
    <span class="editedby">Last edited by <a href="user.php?id=<?=$LoggedUser['ID']?>"><?=$LoggedUser['Username']?></a> just now</span>
</div>
