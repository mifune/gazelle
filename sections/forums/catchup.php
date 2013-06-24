<?
authorize();
if(!isset($_GET['forumid']) || ($_GET['forumid']!='all' && !is_number($_GET['forumid']))) { error(403); }

if ($_GET['forumid']=='all') {
	$DB->query("UPDATE users_info SET CatchupTime='".sqltime()."' WHERE UserID=$LoggedUser[ID]");
	$Cache->delete_value('user_info_'.$LoggedUser['ID']);
	header('Location: forums.php');

} else {
	/* // Insert a value for each topic 
	$DB->query("INSERT INTO forums_last_read_topics (UserID, TopicID, PostID)
				SELECT '$LoggedUser[ID]', ID, LastPostID FROM
				forums_topics
				WHERE (LastPostTime>'".time_minus(3600*24*30)."' OR IsSticky='1')
					AND ForumID = ".$_GET['forumid']."
				ON DUPLICATE KEY UPDATE PostID=LastPostID"); */

	$DB->query("INSERT INTO forums_last_read_topics (UserID, TopicID, PostID)
				SELECT '$LoggedUser[ID]', t.ID, t.LastPostID 
				FROM forums_topics AS t
                        JOIN users_info AS i ON i.UserID=$LoggedUser[ID]
				WHERE (LastPostTime>i.CatchupTime OR i.CatchupTime is null)
					AND ForumID = ".$_GET['forumid']."
				ON DUPLICATE KEY UPDATE PostID=LastPostID");
      
	header('Location: forums.php?action=viewforum&forumid='.$_GET['forumid']);
}
?>
