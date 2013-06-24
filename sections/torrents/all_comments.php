<?
/******************************************************************************/

if (!check_perms('users_mod')) error(403);

//---------- Things to sort out before it can start printing/generating content

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;
 
if (isset($LoggedUser['PostsPerPage'])) {
	$PerPage = $LoggedUser['PostsPerPage'];
} else {
	$PerPage = POSTS_PER_PAGE;
}

list($Page, $Limit) = page_limit($PerPage);
 

// Start printing
show_header('All torrent comments' , 'comments,bbcode,jquery');
?>
<div class="thin"> 
    <h2>Latest Torrent Comments</h2>
<?
 
if ($_GET['order_by']=='id') 
    $ORDERBY = "c.ID";
 else
    $ORDERBY = "c.AddedTime";
 

$DB->query("SELECT SQL_CALC_FOUND_ROWS
                    tg.Name, c.ID, c.GroupID, c.AuthorID, c.AddedTime, c.Body, c.EditedUserID, c.EditedTime ,  u.Username 
              FROM torrents_comments AS c
         LEFT JOIN torrents_group AS tg ON tg.ID=c.GroupID 
		 LEFT JOIN users_main AS u ON u.ID=c.EditedUserID
          ORDER BY $ORDERBY DESC
             LIMIT $Limit");

$Comments = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 

?>
	<div class="linkbox"><a name="comments"></a>
<?
$Pages=get_pages($Page,$NumResults,$PerPage,9);
echo $Pages;
?>
	</div>
<?

//---------- Begin printing
foreach($Comments as $Key => $Post){
	list($TGName, $PostID, $GroupID, $AuthorID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername) = $Post ;
	list($AuthorID, $Username, $PermissionID, $Paranoia, $Donor, $Warned, $Avatar, $Enabled, $UserTitle,,,$Signature) = array_values(user_info($AuthorID));
      $AuthorPermissions = get_permissions($PermissionID);
      list($ClassLevel,$PermissionValues,$MaxSigLength,$MaxAvatarWidth,$MaxAvatarHeight)=array_values($AuthorPermissions);
      // we need to get custom permissions for this author
      //$PermissionValues = get_permissions_for_user($AuthorID, false, $AuthorPermissions);
?>
    <div id="post<?=$PostID?>">
    <div class="head"><a class="post_id" href="torrents.php?id=<?=$GroupID?>"><?=$TGName?></a></div>
<table class="forum_post box vertical_margin<?=$HeavyInfo['DisableAvatars'] ? ' noavatar' : ''?>" >
	<tr class="smallhead">
		<td colspan="2">
			<span style="float:left;"><a class="post_id" href="torrents.php?id=<?=$GroupID?>&amp;postid=<?=$PostID?>#post<?=$PostID?>">#<?=$PostID?></a>
				<?=format_username($AuthorID, $Username, $Donor, $Warned, $Enabled, $PermissionID, $UserTitle, true)?> <?=time_diff($AddedTime)?> <a href="reports.php?action=report&amp;type=torrents_comment&amp;id=<?=$PostID?>">[Report]</a>

<? if ( ($AuthorID == $LoggedUser['ID'] && ( time_ago($AddedTime)<USER_EDIT_POST_TIME || time_ago($EditedTime)<USER_EDIT_POST_TIME ) ) 
                                                                || check_perms('site_moderate_forums') ){ ?>
                        - <a href="#post<?=$PostID?>" onclick="Edit_Form('<?=$PostID?>','<?=$Key?>');">[Edit]</a><? }
  if (check_perms('site_admin_forums')){ ?> 
                        - <a href="#post<?=$PostID?>" onclick="Delete('<?=$PostID?>');">[Delete]</a> <? } ?>
			</span>
			<span id="bar<?=$PostID?>" style="float:right;">
				<a href="#">&uarr;</a>
			</span>
		</td>
	</tr>
	<tr>
<? if(empty($HeavyInfo['DisableAvatars'])) {?>
		<td class="avatar" valign="top" rowspan="2">
	<? if ($Avatar) { ?>
			<img src="<?=$Avatar?>" class="avatar" style="<?=get_avatar_css($MaxAvatarWidth, $MaxAvatarHeight)?>" alt="<?=$Username ?>'s avatar" />
	<? } else { ?>
			<img src="<?=STATIC_SERVER?>common/avatars/default.png" class="avatar" style="<?=get_avatar_css(100, 120)?>" alt="Default avatar" />
	<?
         }
        $UserBadges = get_user_badges($AuthorID); 
        if( !empty($UserBadges) ) {  ?>
               <div class="badges">
<?                  print_badges_array($UserBadges, $AuthorID); ?>
               </div>
<?      }      ?>
		</td>
<?
}
$AllowTags= get_permissions_advtags($AuthorID, false, $AuthorPermissions);
?>
		<td class="postbody" valign="top">
			<div id="content<?=$PostID?>" class="post_container">
                      <div class="post_content"><?=$Text->full_format($Body, $AllowTags) ?> </div>
          
                      
<? if($EditedUserID){ ?>  
                        <div class="post_footer">
<?	if(check_perms('site_moderate_forums')) { ?>
				<a href="#content<?=$PostID?>" onclick="LoadEdit('torrents', <?=$PostID?>, 1); return false;">&laquo;</a> 
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
    </div>
<?	}

  

?>
	<div class="linkbox">
		<?=$Pages?>
	</div>
</div>
<? show_footer(); ?>
