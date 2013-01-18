</div>
<div id="footer">
<? 	if (!empty($Options['disclaimer'])) { ?>
	<br /><br />
	<div id="disclaimer_container" class="thin" style="text-align:center; margin-bottom:20px;">
		None of the files shown here are actually hosted on this server. The links are provided solely by this site's users. These BitTorrent files are meant for the distribution of backup files. By downloading the BitTorrent file, you are claiming that you own the original file. The administrator of this site (http://<?=NONSSL_SITE_URL?>) holds NO RESPONSIBILITY if these files are misused in any way and cannot be held responsible for what its users post, or any other actions of it.
	</div>
<?
	}
	if (count($UserSessions)>1) {
		foreach ($UserSessions as $ThisSessionID => $Session) {
			if ($ThisSessionID != $SessionID) {
				$LastActive = $Session;
				break;
			}
		}
	}
	
?>
	<p>
		Site and design &copy; <?=date("Y")?> <?=SITE_NAME?>
	</p>
	<? if(!empty($LastActive)){ ?><p><a href="user.php?action=sessions" title="Manage Sessions">Last activity <?=time_diff($LastActive['LastUpdate'])?> from <?=$LastActive['IP']?>.</a></p><? } ?>
	
    <?
if (check_perms('users_mod'))
    {
        $Load = sys_getloadavg(); ?>
        <p>
                <strong>Time:</strong> <?=number_format(((microtime(true)-$ScriptStartTime)*1000),5)?> ms
                <strong>Used:</strong> <?=get_size(memory_get_usage(true))?>
                <strong>Load:</strong> <?=number_format($Load[0],2).' '.number_format($Load[1],2).' '.number_format($Load[2],2)?>
                <strong>Date:</strong> <?=time_diff(time(),2,false,false,1)  //date('M d Y, H:i')?>

        </p>
<? } ?>

    <p>
        <a style="margin-left:16px;vertical-align: top" href="feeds.php?feed=torrents_all&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : All Torrents" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="feeds.php?feed=torrents_all&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : All Torrents" >torrents</a>
        
        <a style="margin-left:16px;vertical-align: top" href="feeds.php?feed=feed_news&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : News" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="feeds.php?feed=feed_news&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : News" >news</a>
        
        <a style="margin-left:16px;vertical-align: top" href="feeds.php?feed=torrents_bookmarks_t_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>&amp;name=<?=urlencode(SITE_NAME.': Bookmarked Torrents')?>" title="<?=SITE_NAME?> : Bookmarked Torrents" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="feeds.php?feed=torrents_bookmarks_t_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>&amp;name=<?=urlencode(SITE_NAME.': Bookmarked Torrents')?>" title="<?=SITE_NAME?> : Bookmarked Torrents" >bookmarks</a>
    
        <a style="margin-left:16px;vertical-align: top" href="feeds.php?feed=feed_blog&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : Blog" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="feeds.php?feed=feed_blog&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : Blog" >blog</a>
        
        <a style="margin-left:16px;vertical-align: top" href="feeds.php?feed=torrents_notify_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : Torrent Notifications" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="feeds.php?feed=torrents_notify_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> : Torrent Notifications" >notifications</a>
     
        <a style="margin-left:16px;vertical-align: top" href="articles.php?topic=rsshelp" title="<?=SITE_NAME?> : RSS Help" ><img src="<?=STATIC_SERVER?>/common/symbols/rss.png" alt="RSS feed" /></a>
        <a style="margin-left:3px;" href="articles.php?topic=rsshelp" title="<?=SITE_NAME?> : RSS Help" >help</a>
     
    </p>
    <p><a href="log.php">Site Logs</a></p>
</div>
<div id="footer_bottom">
</div>
    
<? if (DEBUG_MODE || check_perms('site_debug')) { ?>
	<!-- Begin Debugging -->
	<div id="site_debug">
<?
$Debug->flag_table();
$Debug->error_table();
$Debug->sphinx_table();
$Debug->query_table();
$Debug->cache_table();
$Debug->vars_table();
?>
	</div>
	<!-- End Debugging -->
<? } ?>

</div>
<div id="lightbox" class="lightbox hidden"></div>
<div id="curtain" class="curtain hidden"></div>

<!-- Extra divs, for stylesheet developers to add imagery -->
<div id="extra1"><span></span></div>
<div id="extra2"><span></span></div>
<div id="extra3"><span></span></div>
<div id="extra4"><span></span></div>
<div id="extra5"><span></span></div>
<div id="extra6"><span></span></div>
</body>
</html>
