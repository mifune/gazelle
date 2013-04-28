<?

/********************************************************************************
 ************ Torrent form class *************** upload.php and torrents.php ****
 ********************************************************************************
 ** This class is used to create both the upload form, and the 'edit torrent'  **
 ** form. It is broken down into several functions - head(), foot(),           **
 ** music_form() [music], audiobook_form() [Audiobooks and comedy], and	       **
 ** simple_form() [everything else].                                           **
 **                                                                            **
 ** When it is called from the edit page, the forms are shortened quite a bit. **
 **                                                                            **
 ********************************************************************************/
 
class TORRENT_FORM {
        var $NewCategories = array();
	var $Media = array();
	var $NewTorrent = false;
	var $Torrent = array();
	var $Error = false;
	var $TorrentID = false;
	var $Disabled = '';
	
	function TORRENT_FORM($Torrent = false, $Error = false, $NewTorrent = true) {
		
		$this->NewTorrent = $NewTorrent;
		$this->Torrent = $Torrent;
		$this->Error = $Error;
		
		global $NewCategories, $Media, $TorrentID;
		
                $this->NewCategories = $NewCategories;
		$this->Media = $Media;
		$this->TorrentID = $TorrentID;
		
            /* // no reason to disable certain elements if filling from groupinfo is allowed
		if($this->Torrent && $this->Torrent['GroupID']) {
			$this->Disabled = ' disabled="disabled"';
		} */
	}


	function head() {
		global $LoggedUser,$DupeResults;
?>
<a id="uploadform"></a>

<?		if($this->NewTorrent) { ?>
	<p style="text-align: center;">
		Your personal announce url is:<br />
		<input type="text" value="<?= ANNOUNCE_URL.'/'.$LoggedUser['torrent_pass'].'/announce'?>" size="71" onfocus="this.select()" />
	</p>
<?		}
		 
            //for testing form vars set action="http://www.tipjar.com/cgi-bin/test"
?>
      <div id="messagebar" class="messagebar alert<? if(!$this->Error) echo ' hidden'?>"><? if($this->Error) echo ($this->Error) ; ?></div><br />
      <div id="uploadpreviewbody" class="hidden"> 
            <div id="contentpreview" style="text-align:left;"></div>  
	</div>
	<form action="" enctype="multipart/form-data" method="post" id="upload_table" onsubmit="$('#post').raw().disabled = 'disabled'">
      <?
    if (is_array($DupeResults)) {
        $INLINE = true;
		$DupeTitle = $this->Torrent['Title']; 
        include(SERVER_ROOT . '/sections/upload/display_dupes.php');
      ?>
        <div class="box pad shadow center"> 
            If you have checked and are certain these are not dupes check this box to ignore the dupe check<br/>
            <strong>Skip Dupe Check:</strong> <input type="checkbox" name="ignoredupes" value="1" /> *(make sure you enter the same torrent file)
        </div>
        <br/>
      <?
    }
      ?>
		<div>
			<input type="hidden" name="submit" value="true" />
			<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
<?		if(!$this->NewTorrent) { ?>
			<input type="hidden" name="action" value="takeedit" />
			<input type="hidden" name="torrentid" value="<?=display_str($this->TorrentID)?>" />
<?		} else {
			if($this->Torrent && $this->Torrent['GroupID']) { ?>
			<input type="hidden" name="groupid" value="<?=display_str($this->Torrent['GroupID'])?>" />
<?			} 
			if($this->Torrent && $this->Torrent['RequestID']) { ?>
			<input type="hidden" name="requestid" value="<?=display_str($this->Torrent['RequestID'])?>" />
<?			} 
			if($this->Torrent && $this->Torrent['TemplateID']) { ?>
			<input type="hidden" name="templateid" value="<?=display_str($this->Torrent['TemplateID'])?>" />
<?			}  
			if($this->Torrent && $this->Torrent['TemplateFooter']) { ?>
			<input type="hidden" name="templatefooter" value="<?=display_str($this->Torrent['TemplateFooter'])?>" />
<?			}
		} ?>
		</div>
          <div class="head">Upload torrent</div>
		<table cellpadding="3" cellspacing="1" border="0" class="border" width="100%">
<?		if($this->NewTorrent) { ?>
                <tr class="uploadbody">
				<td class="label">
					Torrent file
				</td>
				<td>
					<input id="file" type="file" name="file_input" size="70" /><br/>[max .torrent filesize: 1mb]
				</td>
                </tr>
                <tr class="uploadbody">
                                <td class="label">
                                    Category
                                </td>
                                <td>
                                    <select id="category" name="category" onchange="change_tagtext();">
                                        <option value="0">---</option>
                                    <? foreach($this->NewCategories as $category) { ?>
                                        <option value="<?=$category['id']?>"<? 
                                            if (isset($this->Torrent['Category']) && $this->Torrent['Category']==$category['id']) {
                                                echo ' selected="selected"';
                                            }   ?>><?=$category['name']?></option>
                                    <? } ?>
                                    </select>
                                </td>
			</tr>
		 
<?		}//if ?>
                  
<?	} // function head

	


	

	function simple_form($OfficialTags = '') {
            global $Text, $LoggedUser; 
		$Torrent = $this->Torrent; 
?>		 
<?		if ($this->NewTorrent) {  ?>
			<tr id="name" class="uploadbody">
				<td class="label">Title</td>
				<td>
					<input type="text" id="title" name="title" class="long" value="<?=display_str($Torrent['Title']) ?>" />
				</td>
			</tr>
			<tr class="uploadbody">
				<td class="label">Tags</td>
				<td>
               <?
                $taginfo = get_article('tagrulesinline');
                if($taginfo) echo $Text->full_format($taginfo, true); 
                ?>
                    <div id="tagtext"></div>
<?              if($OfficialTags) { ?>
					<select id="genre_tags" name="genre_tags" onchange="add_tag();return false;" style="vertical-align: top" <?=$this->Disabled?>>
						<option>---</option>
<?                  foreach(display_array($OfficialTags) as $Tag) { ?>
						<option value="<?=$Tag ?>"><?=$Tag ?></option>
<?                  }   ?>
					</select>
<?              } 
                ?>  <!--style="height:1.4em;"-->  
                    <textarea id="tags" name="tags" class="medium" rows="3" <?=$this->Disabled?> ><?=display_str($Torrent['TagList']) ?></textarea>
                    <br />
                </td>
			</tr> 
			<!--<tr id="uploadpreviewbody" class="hidden"> 
				<td colspan="2"> 
                                <div id="contentpreview" style="text-align:left;"></div> 
                        </td>
			</tr> -->
			<tr class="uploadbody">
				<td class="label">Cover Image</td>
                        <td>    <strong>Enter the full url for your image.</strong><br/>
                                    Note: Do not add a thumbnail image as cover, rather leave this field blank if you don't have a good cover image or an image of the actor(s).
                             <input type="text" id="image" class="long" name="image" value="<?=display_str($Torrent['Image']) ?>" <?=$this->Disabled?>/>
                        </td>
			</tr> 
			<tr class="uploadbody">
				<td class="label">Description</td>
				<td> 
                            <? $Text->display_bbcode_assistant("desc", get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions'])); ?>
                             <textarea name="desc" id="desc" class="long" rows="36"><?=display_str($Torrent['GroupDescription']); ?></textarea>
                        </td>
			</tr> 
<?		} ?>
 
<?	}//function simple_form




	function foot() {
		$Torrent = $this->Torrent;
?> 
	 
<?		 
			if(check_perms('torrents_freeleech')) {
?>
			<tr id="freetorrent" class="uploadbody">
				<td class="label">Freeleech</td>
				<td>
					<select name="freeleech">
<?	$FL = array("Normal", "Free");    //, "Neutral");
	foreach($FL as $Key => $Name) { ?>	
						<option value="<?=$Key?>" <?=($Key == $Torrent['FreeTorrent'] ? ' selected="selected"' : '')?>><?=$Name?></option>
<?	} ?>
					</select>
				</td>
			</tr>
<?
			}
		 
?>
			<tr>
				<td colspan="2" style="text-align: center;">
					<p>Be sure that your torrent is approved by the <a href="articles.php?topic=upload">rules</a>. Not doing this will result in a <strong>warning</strong> or <strong>worse</strong>.</p>
<?		if($this->NewTorrent) { ?>
					<p>After uploading the torrent, you will have a one hour grace period during which no one other than you can fill requests with this torrent. Make use of this time wisely, and search the requests. </p>
 
                              <input id="post_preview" type="button" value="Preview" onclick="if(this.preview){Upload_Quick_Edit();}else{Upload_Quick_Preview();}" />
    <?		} ?>	
					<input id="post" name="submit" type="submit" <? if($this->NewTorrent) { echo "value=\"Upload torrent\""; } else { echo "value=\"Edit torrent\"";} ?> />
				</td>
			</tr>
		</table>
	</form>

<?	} //function foot
	
	  
}//class
?>
