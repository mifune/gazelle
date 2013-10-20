<?

/********************************************************************************
 ************ Permissions form ********************** user.php and tools.php ****
 ********************************************************************************
 ** This function is used to create both the class permissions form, and the   **
 ** user custom permissions form.					       **
 ********************************************************************************/
 
 $PermissionsArray = array(
	'site_leech' => 'Can leech (Does this work?).',
	'site_upload' => 'Upload torrent access.',
     
	'use_templates' => 'Can use templates.',
	'make_private_templates' => 'Can make/delete private upload templates.',
	'make_public_templates' => 'Can make public upload templates.',
	'delete_any_template' => 'Can delete any upload templates.',
     
	'site_view_stats' => 'View the site stats page.',
	'site_stats_advanced' => 'View selected site stats.',
     
	'site_vote' => 'Can vote on requests.',
	'site_submit_requests' => 'Request create access.',
	'site_see_old_requests' => 'Can see old requests.',
     
	'site_staff_page' => 'Can see the Staff page.',
     
	'site_advanced_search' => 'Advanced search access.',
	'site_top10' => 'Top 10 access.',
	'site_advanced_top10' => 'Advanced Top 10 access.',
	'site_torrents_notify' => 'Notifications access.',
     
	'site_collages_create' => 'Can create collages.',
	'site_collages_delete' => 'Can delete collages.',
	'site_collages_subscribe' => 'Collage subscription access.',
	'site_collages_personal' => 'Can have a personal collage.',
	'site_collages_renamepersonal' => 'Can rename own personal collages.',
     
	'site_make_bookmarks' => 'Bookmarks access.',
	'site_can_invite_always' => 'Can invite past user limit.',
	'site_send_unlimited_invites' => 'Unlimited invites.',
            'site_advanced_tags' => 'Advanced bbcode tags.',
	'site_edit_own_posts' => 'Can edit own posts in forum after edit time limit.',
     
     'site_ignore_floodcheck' => 'Can post more often than floodcheck allows',
	'site_moderate_requests' => 'Request moderation access.',
	'site_moderate_forums' => 'Forum moderation access.',
	'site_admin_forums' => 'Forum administrator access.',
	'site_forums_double_post' => 'Can double post in the forums.',
	'site_view_flow' => 'Can view stats and data pools.',
	'site_view_full_log' => 'Can view old log entries.',
	'site_view_torrent_snatchlist' => 'Can view torrent snatchlists.',
     
	'site_view_torrent_peerlist' => 'Can view torrent peerlists.',
     
            'site_vote_tag' => 'Can vote on tags.',
            'site_add_tag' => 'Can add tags.',
            'site_add_multiple_tags' => 'Can add multiple tags at once.',
	'site_delete_tag' => 'Can delete tags.',
	'site_vote_tag_enhanced' => 'Has extra tag voting power (&plusmn;'. ENHANCED_VOTE_POWER . ')',
            'site_manage_tags' => 'Can manage official tag list and synonyms.',
            'site_convert_tags' => 'Can convert tags to synonyms.',
     
            'site_manage_shop' => 'Can manage shop.',
            'site_manage_badges' => 'Can manage badges.',
            'site_manage_awards' => 'Can manage awards schedule.',
     
	'site_disable_ip_history' => 'Disable IP history.',
	'zip_downloader' => 'Download multiple torrents at once.',
	'site_debug' => 'Developer access.',
	'site_proxy_images' => 'Image proxy & Anti-Canary.',
	'site_search_many' => 'Can go past low limit of search results.',
	'site_play_slots' => 'Can play the slot machine.',
    'site_set_language' => 'Can set own user language(s) in settings',

    'site_torrent_signature' => 'Can set and use a torrent signature',
     
     
	'users_edit_usernames' => 'Can edit usernames.',
	'users_edit_ratio' => 'Can edit other\'s upload/download amounts.',
	'users_edit_own_ratio' => 'Can edit own upload/download amounts.',
     
      'users_edit_tokens' => 'Can edit other\'s FLTokens (Slots?)',
      'users_edit_own_tokens' => 'Can edit own FLTokens (Slots?)',
      'users_edit_pfl' => 'Can edit other\'s personal freeleech',
      'users_edit_own_pfl' => 'Can edit own personal freeleech',
      'users_edit_credits' => 'Can edit other\'s Bonus Credits',
      'users_edit_own_credits' => 'Can edit own Bonus Credits',
     
	'users_edit_titles' => 'Can edit titles.',
	'users_edit_avatars' => 'Can edit avatars.',
        'users_edit_badges' => 'Can edit other\s badges.',
        'users_edit_own_badges' => 'Can edit own badges.',
     
	'users_edit_invites' => 'Can edit invite numbers and cancel sent invites.',
	'users_edit_watch_hours' => 'Can edit contrib watch hours.',
	'users_edit_reset_keys' => 'Can reset passkey/authkey.',
	'users_edit_profiles' => 'Can edit anyone\'s profile.',
 	'users_view_friends' => 'Can view anyone\'s friends.',
	'users_reset_own_keys' => 'Can reset own passkey/authkey.',
	'users_edit_password' => 'Can change passwords.',
	'users_edit_email' => 'Can change user email address.',
     
	'users_promote_below' => 'Can promote users to below current level.',
	'users_promote_to' => 'Can promote users up to current level.',
        'user_group_permissions'=> 'Can manage group permissions.',
	'users_view_donor' => 'Can view users my donations page.',
    'users_give_donor' => 'Can manually give donor status.',
	'users_warn' => 'Can warn users.',
	'users_disable_users' => 'Can disable users.',
	'users_disable_posts' => 'Can disable users\' posting rights.',
	'users_disable_any' => 'Can disable any users\' rights.',
	'users_delete_users' => 'Can delete users.',
	'users_view_invites' => 'Can view who user has invited.',
	'users_view_seedleech' => 'Can view what a user is seeding or leeching.',
            'users_view_bonuslog' => 'Can view bonus logs.',
	'users_view_uploaded' => 'Can view a user\'s uploads, regardless of privacy level.',
	'users_view_keys' => 'Can view passkeys.',
	'users_view_ips' => 'Can view IP addresses.',
	'users_view_email' => 'Can view email addresses.',
	'users_override_paranoia' => 'Can override paranoia.',
	'users_logout' => 'Can log users out (old?).',
	'users_make_invisible' => 'Can make users invisible.',
	'users_mod' => 'Basic moderator tools.',
	'users_groups' => 'Can use Group tools.',
	'users_manage_cheats' => 'Can manage watchlist.',
    'users_set_suppressconncheck' => 'Can set Suppress ConnCheck prompt for users.',
    'users_view_language' => 'Can view user language(s) on user profile',
     
	'torrents_edit' => 'Can edit any torrent.',
        'torrents_review' => 'Can mark torrents for deletion.',
        'torrents_review_override' => 'Can overide ongoing marked for deletion process.',
        'torrents_review_manage' => 'Can set site options for marked for deletion list.',
        'torrents_download_override' => 'Can download torrents that are marked for deletion.',
     
	'torrents_delete' => 'Can delete torrents.',
	'torrents_delete_fast' => 'Can delete more than 3 torrents at a time.',
	'torrents_freeleech' => 'Can make torrents freeleech.',
	'torrents_search_fast' => 'Rapid search (for scripts).',
	'torrents_hide_dnu' => 'Hide the Do Not Upload list by default.',
	'torrents_hide_imagehosts' => 'Hide the Imagehost Whitelist list by default.',
     
        'admin_manage_site_options' => 'Can manage site options',
        'admin_manage_languages' => 'Can manage the official site languages',
        'admin_email_blacklist' => 'Can manage the email blacklist',
        'admin_manage_cheats' => 'Can admin watchlist.',
        'admin_manage_categories' => 'Can manage categories.',
	'admin_manage_news' => 'Can manage news.',
        'admin_manage_articles' => 'Can manage articles',
	'admin_manage_blog' => 'Can manage blog.',
	'admin_manage_polls' => 'Can manage polls.',
	'admin_manage_forums' => 'Can manage forums (add/edit/delete).',
	'admin_manage_fls' => 'Can manage FLS.',
	'admin_reports' => 'Can access reports system.',
	'admin_advanced_user_search' => 'Can access advanced user search.',
	'admin_create_users' => 'Can create users through an administrative form.',
	'admin_donor_drives' => 'Can view and manage donation drives.',
	'admin_donor_log' => 'Can view and manage the donor log.',
	'admin_donor_addresses' => 'Can manage and enter new bitcoin addresses.',
	'admin_manage_ipbans' => 'Can manage IP bans.',
	'admin_dnu' => 'Can manage do not upload list.',
	  'admin_imagehosts' => 'Can manage Imagehost Whitelist.',
	'admin_clear_cache' => 'Can clear cached.',
	'admin_whitelist' => 'Can manage the list of allowed clients.',
	'admin_manage_permissions' => 'Can edit permission classes/user permissions.',
	'admin_schedule' => 'Can run the site schedule.',
	'admin_login_watch' => 'Can manage login watch.',
	'admin_manage_wiki' => 'Can manage wiki access.',
	'admin_update_geoip' => 'Can update geoip data.',
	'site_collages_manage' => 'Can manage any collage.',
 	'site_collages_recover' => 'Can recover \'deleted\' collages.',
 	'edit_unknowns' => 'Can edit unknown release information.',
 	'forums_polls_create' => 'Can create polls in the forums.',
 	'forums_polls_moderate' => 'Can feature and close polls.',
	'project_team' => 'Is part of the project team.'

 );
 
function permissions_form(){ ?>
<div class="permissions">
	<div class="permission_container">
		<table>
			<tr>
				<td class="colhead">Site</td>
			</tr>
			<tr>
				<td>
					<? display_perm('site_leech','Can leech.'); ?>
					<? display_perm('site_upload','Can upload.'); ?>
                            
					<? display_perm('use_templates','Can use templates.'); ?>
					<? display_perm('make_private_templates','Can make/delete private upload templates.'); ?>
					<? display_perm('make_public_templates','Can make public upload templates.'); ?>
					<? display_perm('delete_any_template','Can delete any upload templates.'); ?>

                              <? display_perm('site_view_stats' , 'View the site stats page.'); ?> 
                              <? display_perm('site_stats_advanced', 'View selected site stats.'); ?> 
      
					<? display_perm('site_vote','Can vote on requests.'); ?>
					<? display_perm('site_submit_requests','Can submit requests.'); ?>
					<? display_perm('site_see_old_requests','Can see old requests.'); ?>
                    
					<? display_perm('site_staff_page','Can see the Staff page.'); ?>
    
					<? display_perm('site_advanced_search','Can use advanced search.'); ?>
					<? display_perm('site_top10','Can access top 10.'); ?>
					<? display_perm('site_torrents_notify','Can access torrents notifications system.'); ?>
					<? display_perm('site_collages_create','Can create collages.'); ?>
					<? display_perm('site_collages_delete','Can delete collages.'); ?>
					<? display_perm('site_collages_subscribe','Can access collage subscriptions.'); ?>
					<? display_perm('site_collages_personal','Can have a personal collage.'); ?>
					<? display_perm('site_collages_renamepersonal','Can rename own personal collages.'); ?>
					<? display_perm('site_advanced_top10','Can access advanced top 10.'); ?>
					<? display_perm('site_make_bookmarks','Can make bookmarks.'); ?>
					<? display_perm('site_can_invite_always', 'Can invite users even when invites are closed.'); ?>
					<? display_perm('site_send_unlimited_invites', 'Can send unlimited invites.'); ?>
                              <?            display_perm('site_advanced_tags', 'Can use advanced bbcode tags.'); ?>
                              <?            display_perm('site_edit_own_posts', 'Can edit own posts in forum after edit lock time limit.'); ?>
                                <? display_perm('site_ignore_floodcheck', 'Can post more often than floodcheck allows', 'Allows multiple posting immediately - no complaints if you double post!') ; ?> 
                              <? display_perm('site_moderate_requests', 'Can moderate any request.'); ?>
					<? display_perm('forums_polls_create','Can create polls in the forums.') ?>
					<? display_perm('forums_polls_moderate','Can feature and close polls.') ?>
					<? display_perm('site_moderate_forums', 'Can moderate the forums', 'Can moderate the forums (lock/sticky/rename/move threads).'); ?>
					<? display_perm('site_admin_forums', 'Can administrate the forums.','Can administrate the forums (merge/delete threads).'); ?>
					<? display_perm('site_view_flow', 'Can view site stats and data pools.'); ?>
					<? display_perm('site_view_full_log', 'Can view the full site log.'); ?>
					<? display_perm('site_view_torrent_snatchlist', 'Can view torrent snatchlists.'); ?>
                    
					<? display_perm('site_view_torrent_peerlist', 'Can view torrent peerlists.'); ?>
    
                                          <? display_perm('site_vote_tag', 'Can vote on tags.'); ?>
                                          <? display_perm('site_add_tag', 'Can add tags.'); ?>
            <? display_perm('site_add_multiple_tags','Can add multiple tags at once.'); ?>
					<? display_perm('site_delete_tag', 'Can delete tags.'); ?>
					<? display_perm('site_vote_tag_enhanced', 'Has extra tag voting power (&plusmn;'. ENHANCED_VOTE_POWER . ')','extra tag voting power is defined in config'); ?>
 
                                         
					<? display_perm('site_disable_ip_history', 'Disable IP history.'); ?>
					<? display_perm('zip_downloader', 'Download multiple torrents at once.'); ?>
					<? display_perm('site_debug', 'View site debug tables.'); ?>
					<? display_perm('site_proxy_images', 'Proxy images through the server.'); ?>
					<? display_perm('site_search_many', 'Can go past low limit of search results.'); ?>
					<? display_perm('site_collages_manage','Can manage/edit any collage.'); ?>
					<? display_perm('site_collages_recover', 'Can recover \'deleted\' collages.'); ?>
					<? display_perm('site_forums_double_post', 'Can double post in the forums.'); ?>
					<? display_perm('project_team', 'Part of the project team.'); ?>
					<? display_perm('site_play_slots', 'Can play the slot machine.'); ?> 
					<? display_perm('site_set_language', 'Can set own user language(s).', 'Can set own user language(s) on settings page.'); ?> 
                    <? display_perm('site_torrent_signature', 'Can set and use a torrent signature.'); ?> 
  
				</td>
			</tr>
		</table>
	</div>
	<div class="permission_container">
		<table>
			<tr>
				<td class="colhead">Users</td>
			</tr>
			<tr>
				<td>
					<? display_perm('users_edit_usernames', 'Can edit usernames.'); ?>
					<? display_perm('users_edit_ratio', 'Can edit other\'s upload/download amounts.'); ?>
					<? display_perm('users_edit_own_ratio', 'Can edit own upload/download amounts.'); ?>
					
                                        <? display_perm('users_edit_tokens', 'Can edit other\'s FLTokens (Slots?)'); ?>
					<? display_perm('users_edit_own_tokens', 'Can edit own FLTokens (Slots?)'); ?>
                                        <? display_perm('users_edit_pfl', 'Can edit other\'s personal freeleech'); ?>
                                        <? display_perm('users_edit_own_pfl', 'Can edit own personal freeleech'); ?>
					<? display_perm('users_edit_credits', 'Can edit other\'s Bonus Credits.'); ?>
					<? display_perm('users_edit_own_credits', 'Can edit own Bonus Credits.'); ?>
      					
                                        <? display_perm('users_edit_titles', 'Can edit titles.'); ?>
					<? display_perm('users_edit_avatars', 'Can edit avatars.'); ?>
                                        <? display_perm('users_edit_badges', 'Can edit other\'s badges.'); ?>
                                        <? display_perm('users_edit_own_badges', 'Can edit own badges.'); ?>
                            
					<? display_perm('users_edit_invites', 'Can edit invite numbers and cancel sent invites.'); ?>
					<? display_perm('users_edit_watch_hours', 'Can edit contrib watch hours.'); ?>
					<? display_perm('users_edit_reset_keys', 'Can reset any passkey/authkey.'); ?>
					<? display_perm('users_edit_profiles', 'Can edit anyone\'s profile.'); ?>
					<? display_perm('users_view_friends', 'Can view anyone\'s friends.'); ?>
					<? display_perm('users_reset_own_keys', 'Can reset own passkey/authkey.'); ?>
					<? display_perm('users_edit_password', 'Can change password.'); ?>
					<? display_perm('users_edit_email', 'Can change user email address.'); ?>
                    
					<? display_perm('users_promote_below', 'Can promote users to below current level.'); ?>
					<? display_perm('users_promote_to', 'Can promote users up to current level.'); ?>
                                        <? display_perm('user_group_permissions', 'Can manage group permissions.', 'Can change a users group permission setting.'); ?> 
					<? display_perm('users_view_donor', 'Can view users my donations page.','Can view detailed donation information for each user'); ?>
					<? display_perm('users_give_donor', 'Can give donor status.','Can manually give donor status'); ?>
					<? display_perm('users_warn', 'Can warn users.'); ?>
					<? display_perm('users_disable_users', 'Can disable users.'); ?>
					<? display_perm('users_disable_posts', 'Can disable users\' posting rights.'); ?>
					<? display_perm('users_disable_any', 'Can disable any users\' rights.'); ?>
					<? display_perm('users_delete_users', 'Can delete anyone\'s account'); ?>
					<? display_perm('users_view_invites', 'Can view who user has invited'); ?>
					<? display_perm('users_view_seedleech', 'Can view what a user is seeding or leeching'); ?>
                                    <? display_perm('users_view_bonuslog', 'Can view a users bonus logs.'); ?>
					<? display_perm('users_view_uploaded', 'Can view a user\'s uploads, regardless of privacy level'); ?>
					<? display_perm('users_view_keys', 'Can view passkeys'); ?>
					<? display_perm('users_view_ips', 'Can view IP addresses'); ?>
					<? display_perm('users_view_email', 'Can view email addresses'); ?>
					<? display_perm('users_override_paranoia', 'Can override paranoia'); ?>
					<? display_perm('users_make_invisible', 'Can make users invisible'); ?>
					<? display_perm('users_logout', 'Can log users out'); ?>
					<? display_perm('users_mod', 'Can access basic moderator tools','Allows access to the user moderation panels'); ?>
					<? display_perm('users_admin_notes', 'Can edit Admin comment','To be used sparingly - staff can add notes via the submit panel'); ?>
					<? display_perm('users_groups', 'Can use Group tools'); ?>
                    <? display_perm('users_manage_cheats', 'Can manage watchlist', 'Can add and remove users from watchlist, and view speed reports page'); ?>
                    <? display_perm('users_set_suppressconncheck', 'Can set Suppress ConnCheck prompt for users', 'Suppress ConnCheck if set for a user stops any prompts in the header bar re: connectable status'); ?>
                    <? display_perm('users_view_language', 'Can view user language(s) on user profile', 'Can view user language(s) on user profile - to other users they can only be seen on the staff page'); ?>
  
					<br/>*Everything is only applicable to users with the same or lower class level
				</td>
			</tr>
		</table>
	</div>
	<div class="permission_container">
		<table>
			<tr>
				<td class="colhead">Torrents</td>
			</tr>
			<tr>
				<td>
			
					<? display_perm('torrents_edit', 'Can edit any torrent'); ?>
                                    <? display_perm('torrents_review', 'Can mark torrents for deletion.'); ?>
                                    <? display_perm('torrents_review_override', 'Can overide ongoing marked for deletion process.'); ?>
                                    <? display_perm('torrents_review_manage', 'Can set site options for marked for deletion list.'); ?>
                                    <? display_perm('torrents_download_override', 'Can download torrents that are marked for deletion.'); ?>
 
					<? display_perm('torrents_delete', 'Can delete torrents'); ?>
					<? display_perm('torrents_delete_fast', 'Can delete more than 3 torrents at a time.'); ?>
					<? display_perm('torrents_freeleech', 'Can make torrents freeleech'); ?>
					<? display_perm('torrents_search_fast', 'Unlimit search frequency (for scripts).'); ?>
					<? display_perm('edit_unknowns', 'Can edit unknown release information.'); ?>
					<? display_perm('site_add_logs', 'Can add logs to torrents after upload'); ?>
					<? display_perm('torrents_hide_dnu', 'Hide the do not upload list by default.'); ?>
					<? display_perm('torrents_hide_imagehosts', 'Hide the imagehost whitelist by default.'); ?>
				
                        </td> 
			</tr>
		</table>
	</div>
	<div class="permission_container">
		<table>
			<tr>
				<td class="colhead">Administrative</td>
			</tr>
			<tr>
				<td>
                                        <? display_perm('admin_manage_site_options', 'Can manage site options'); ?>
                                        <? display_perm('admin_manage_languages', 'Can manage the official site languages'); ?>
                                        <? display_perm('admin_email_blacklist', 'Can manage the email blacklist'); ?>
                                        <? display_perm('admin_manage_cheats', 'Can admin watchlist.', 'Can change site options for watchlist'); ?>
                                        <? display_perm('admin_manage_categories', 'Can manage categories.'); ?>
					<? display_perm('admin_manage_news', 'Can manage news'); ?>
                                        <? display_perm('admin_manage_articles', 'Can manage articles'); ?>
					<? display_perm('admin_manage_blog', 'Can manage blog'); ?>
					<? display_perm('admin_manage_polls', 'Can manage polls'); ?>
					<? display_perm('admin_manage_forums', 'Can manage forums (add/edit/delete)'); ?>
					<? display_perm('admin_manage_fls', 'Can manage FLS'); ?>
                            
                                        <? display_perm('site_manage_tags', 'Can manage official tag list and synonyms.'); ?>
                                        <? display_perm('site_convert_tags', 'Can convert tags to synonyms.'); ?>
                                        <? display_perm('site_manage_badges', 'Can manage badges.'); ?>
                                        <? display_perm('site_manage_awards', 'Can manage awards schedule.'); ?>
                                        <? display_perm('site_manage_shop', 'Can manage bonus shop items.'); ?>
                                         
					<? display_perm('admin_reports', 'Can access reports system'); ?>
					<? display_perm('admin_advanced_user_search', 'Can access advanced user search'); ?>
					<? display_perm('admin_create_users', 'Can create users through an administrative form'); ?>
					<? display_perm('admin_donor_drives', 'Can view and manage donation drives'); ?>
					<? display_perm('admin_donor_log', 'Can view and manage the donor log'); ?>
                    <? display_perm('admin_donor_addresses', 'Can manage and enter new bitcoin addresses.'); ?>
					<? display_perm('admin_manage_ipbans', 'Can manage IP bans'); ?>
					<? display_perm('admin_dnu', 'Can manage do not upload list'); ?> 
					    <? display_perm('admin_imagehosts', 'Can manage imagehosts whitelist'); ?> 
					<? display_perm('admin_clear_cache', 'Can clear cached pages'); ?>
					<? display_perm('admin_whitelist', 'Can manage the list of allowed clients.'); ?>
					<? display_perm('admin_manage_permissions', 'Can edit permission classes/user permissions.', 'Can edit all permissions and templates; user classes / group permissions / individual user permissions.'); ?>
					<? display_perm('admin_schedule', 'Can run the site schedule.'); ?>
					<? display_perm('admin_login_watch', 'Can manage login watch.'); ?>
					<? display_perm('admin_manage_wiki', 'Can manage wiki access.'); ?>
					<? display_perm('admin_update_geoip', 'Can update geoip data.'); ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="submit_container"><input type="submit" name="submit" value="Save Permission Class" /></div>
</div>
<? } ?>
