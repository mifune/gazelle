<?
//TODO: make this use the cache version of the thread, save the db query
/*********************************************************************\
//--------------Get Post--------------------------------------------//

This gets the raw BBCode of a post. It's used for editing and 
quoting posts. 

It gets called if $_GET['action'] == 'get_post'. It requires 
$_GET['post'], which is the ID of the post.

\*********************************************************************/

// Quick SQL injection check
if(!$_GET['post'] || !is_number($_GET['post'])){
	error(0);
}

// Variables for database input
$PostID = (int)$_GET['post'];

// Mainly 
$DB->query("SELECT
		p.Body, t.ForumID
		FROM forums_posts as p JOIN forums_topics as t on p.TopicID = t.ID
		WHERE p.ID='$PostID'");
list($Body, $ForumID) = $DB->next_record(MYSQLI_NUM);

// Is the user allowed to view the post?
if(!check_forumperm($ForumID)) {
	error(0);
}

// This gets sent to the browser, which echoes it wherever 

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
