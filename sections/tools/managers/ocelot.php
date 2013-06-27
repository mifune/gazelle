<?
// Lanz: This code is not used any longer
// but I'll keep it incase we need something similar in the future
/*	$Key = $_REQUEST['key'];
	$Type = $_REQUEST['type'];
	
	if (($Key != TRACKER_SECRET) || ($_SERVER['REMOTE_ADDR'] != TRACKER_HOST && $_SERVER['REMOTE_HOST'] != TRACKER_HOST)) {
		error(403);
	}
	
	switch ($Type) {
		case 'expiretoken':
			$TorrentID = $_REQUEST['torrentid'];
			$UserID = $_REQUEST['userid'];
			if (!is_number($TorrentID) || !is_number($UserID)) {
				error(403);
			}

			$DB->query("UPDATE users_freeleech SET Expired=TRUE WHERE UserID=$UserID AND TorrentID=$TorrentID");
			$Cache->delete_value('users_tokens_'.$UserID);
			break;
	}
 */
?>

