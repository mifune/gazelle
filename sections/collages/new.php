<?
include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;
show_header('Create a collage','bbcode,jquery');

if (!check_perms('site_collages_renamepersonal')) {
	$ChangeJS = "OnChange=\"if ( this.options[this.selectedIndex].value == '0') { $('#namebox').hide(); $('#personal').show(); } else { $('#namebox').show(); $('#personal').hide(); }\"";
}

$Name        = $_REQUEST['name'];
$Category    = $_REQUEST['cat'];
$Description = $_REQUEST['descr'];
$Tags        = $_REQUEST['tags'];
$Error       = $_REQUEST['err'];

if (!check_perms('site_collages_renamepersonal') && $Category === '0') {
	$NoName = true;
}
?>
<div class="thin">
    <h2>Create Collage</h2>
<?
if (!empty($Error)) { ?>
	<div class="save_message error"><?=display_str($Error)?></div>
	<br />
<? } ?>
        <div class="head">New collage</div>
	<form action="collages.php" method="post" name="newcollage">
		<input type="hidden" name="action" value="new_handle" />
		<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
		<table>
			<tr id="collagename">
				<td class="label"><strong>Name</strong></td>
				<td>
					<input type="text" class="long<?=$NoName?' hidden':''?>" name="name" id="namebox" value="<?=display_str($Name)?>" />
					<span id="personal" class="<?=$NoName?'':'hidden'?>" style="font-style: oblique"><strong><?=$LoggedUser['Username']?>'s personal collage</strong></span>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Category</strong></td>
				<td>
					<select name="category" <?=$ChangeJS?>>
<?
array_shift($CollageCats);
		
foreach($CollageCats as $CatID=>$CatName) { ?>
						<option value="<?=$CatID+1?>"<?=(($CatID+1 == $Category)?' selected':'')?>><?=$CatName?></option>
<? } 
$DB->query("SELECT COUNT(ID) FROM collages WHERE UserID='$LoggedUser[ID]' AND CategoryID='0' AND Deleted='0'");
list($CollageCount) = $DB->next_record();
if(($CollageCount < $LoggedUser['Permissions']['MaxCollages']) && check_perms('site_collages_personal')) { ?>
						<option value="0"<?=(($Category === '0')?' selected':'')?>>Personal</option>
<? } ?>
					</select>
					<br />
					<ul>
						<li><strong>Theme</strong> - A collage containing releases that all relate to a certain theme</li>	
						<li><strong>Porn Star</strong> - A collage containing a specific porn star</li>
						<li><strong>Studio</strong> - A collage with content from a specific studio</li>
						<li><strong>Staff picks</strong> - A list of recommendations picked by the staff on special occasions</li>						
<?
   if(($CollageCount < $LoggedUser['Permissions']['MaxCollages']) && check_perms('site_collages_personal')) { ?>
						<li><strong>Personal</strong> - You can put whatever your want here.  It's your personal collage.</li>	
<? } ?>					
					</ul>
				</td>
			</tr>
			<tr>
				<td class="label">Editing Permissions</td>
				<td> 
                            who can add/delete torrents <br/> 
                            <select name="permission">
<?
                                foreach ($ClassLevels as $CurClass) { 
                                    if ($CurClass['Level']>=500) break;
?>
                                    <option value="<?=$CurClass['Level']?>"><?=$CurClass['Name'];?></option>
<?                              } ?>

                                <option value="0" selected="selected">Only Creator</option>
                            </select>
				</td>
			</tr>
			<tr>
				<td class="label">Description</td>
				<td>
                            <div id="preview" class="box pad hidden"></div>
                            <div  id="editor">
                            <? $Text->display_bbcode_assistant("description", get_permissions_advtags($UserID)); ?>
					<textarea name="description" id="description" class="long" rows="10"><?=display_str($Description)?></textarea>
                            </div>
				</td>
			</tr>
			<tr>
				<td class="label"><strong>Tags</strong></td>
				<td>
					<input type="text" id="tags" name="tags" class="long" value="<?=display_str($Tags)?>" />
                                        <p class="min_padding">Space-separated list - eg. <em>hardcore big.tits anal</em></p>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center">
					<strong>Please ensure your collage will be allowed under the <a href="articles.php?topic=collages">rules</a></strong>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="center">
                            <input id="previewbtn" type="button" value="Preview" onclick="Preview_Collage();" />
                            <input type="submit" value="Create collage" />
                        </td>
			</tr>
		</table>
	</form>
</div>
<? show_footer(); ?>
