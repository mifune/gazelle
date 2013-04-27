<?
//*********************************************************************//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Upload form ~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
// This page relies on the TORRENT_FORM class. All it does is call	 //
// the necessary functions.											//
//---------------------------------------------------------------------//
// $Properties, $Err are set in takeupload.php, and                     //
// are only used when the form doesn't validate and this page must be  //
// called again.													   //
//*********************************************************************//

include(SERVER_ROOT.'/sections/upload/functions.php'); 
        
ini_set('max_file_uploads', '100');
show_header('Upload', 'upload,bbcode');

if (empty($Properties) && !empty($_POST['fill']) && is_number($_POST['template']) && check_perms('use_templates') ) {
    /* -------  Get template ------- */
    $TemplateID = (int)$_POST['template'];
    $Properties = $Cache->get_value('template_' . $TemplateID);
    if ($Properties === FALSE) {
        $DB->query("SELECT 
                                    t.ID,
                                    t.UserID,
                                    t.Name, 
                                    t.Title, 
                                    t.CategoryID AS Category,
                                    t.Title,
                                    t.Image,
                                    t.Body AS GroupDescription,
                                    t.Taglist AS TagList,
                                    t.TimeAdded,
                                    t.Public,
                                    u.Username AS Authorname
                               FROM upload_templates as t
                          LEFT JOIN users_main AS u ON u.ID=t.UserID
                              WHERE t.ID='$TemplateID'");
        list($Properties) = $DB->to_array(false, MYSQLI_BOTH);
        if($Properties){
            $Properties['TemplateFooter'] = "[bg=#0074b7][bg=#0074b7,90%][color=white][align=right][b][i][font=Courier New]$Properties[Name] template by $Properties[Authorname][/font][/i][/b][/align][/color][/bg][/bg]";
            $Properties['TemplateID'] = $TemplateID;
            $Cache->cache_value('template_' .$TemplateID, $Properties, 96400 * 7);
        } else { // catch the case where a public template has been unexpectedly removed but left in a random users cache
            $Cache->delete_value('templates_ids_' .$LoggedUser['ID']); // remove from their template list
            $Err = "That template has been deleted - sorry!";
        }
    }
    if ($Properties) {
        //list($Properties) = $Template;
        // only the uploader can use this to prefill (if not a public template)
        if ($Properties['Public']==0 && $Properties['UserID'] != $LoggedUser['ID']) {
            unset($Properties); 
        } 
    }
    
} elseif (empty($Properties) && !empty($_GET['groupid']) && is_number($_GET['groupid'])) {
    $DB->query("SELECT 
		tg.ID as GroupID,
		tg.NewCategoryID AS Category,
		tg.Name AS Title,
		tg.Image AS Image,
		tg.Body AS GroupDescription,
            t.UserID
		FROM torrents_group AS tg
		LEFT JOIN torrents AS t ON t.GroupID = tg.ID
		WHERE tg.ID='$_GET[groupid]'");
    if ($DB->record_count()) {
        list($Properties) = $DB->to_array(false, MYSQLI_BOTH);
        // only the uploader can use this to prefill
        if ($Properties['UserID'] != $LoggedUser['ID']) {
            unset($Properties);
            unset($_GET['groupid']);
        } else {
            $DB->query("SELECT 
                      GROUP_CONCAT(tags.Name SEPARATOR ', ') AS TagList 
                      FROM torrents_tags AS tt JOIN tags ON tags.ID=tt.TagID
                      WHERE tt.GroupID='$_GET[groupid]'");

            list($Properties['TagList']) = $DB->next_record();
        }
    } else {
        unset($_GET['groupid']);
    }
    if (!empty($_GET['requestid']) && is_number($_GET['requestid'])) {
        $Properties['RequestID'] = $_GET['requestid'];
    }
} elseif (empty($Properties) && !empty($_GET['requestid']) && is_number($_GET['requestid'])) {
    include(SERVER_ROOT . '/sections/requests/functions.php');
    $DB->query("SELECT
		r.ID AS RequestID,
		r.CategoryID,
		r.Title AS Title,
		r.Image
		FROM requests AS r
		WHERE r.ID=" . $_GET['requestid']);

    list($Properties) = $DB->to_array(false, MYSQLI_BOTH);
    $Properties['TagList'] = implode(" ", get_request_tags($_GET['requestid']));
}

require(SERVER_ROOT . '/classes/class_torrent_form.php');
$TorrentForm = new TORRENT_FORM($Properties, $Err);

if (!isset($Text)) {
    include(SERVER_ROOT . '/classes/class_text.php'); // Text formatting class
    $Text = new TEXT;
}

$GenreTags = $Cache->get_value('genre_tags_upload');
if (!$GenreTags) {
    $DB->query("SELECT Name FROM tags WHERE TagType='genre' ORDER BY Name");
    $GenreTags = $DB->collect('Name');
    $Cache->cache_value('genre_tags_upload', $GenreTags, 3600 * 6);
}

/* -------  Draw a box with do_not_upload list  -------   */
$DNU = $Cache->get_value('do_not_upload_list');
if ($DNU === FALSE) {
    $DB->query("SELECT 
              d.Name, 
              d.Comment,
              d.Time
              FROM do_not_upload as d
              ORDER BY d.Time");
    $DNU = $DB->to_array();
    $Cache->cache_value('do_not_upload_list', $DNU);
}
list($Name, $Comment, $Updated) = end($DNU);
reset($DNU);
$DB->query("SELECT IF(MAX(t.Time) < '$Updated' OR MAX(t.Time) IS NULL,1,0) FROM torrents AS t
			WHERE UserID = " . $LoggedUser['ID']);
list($NewDNU) = $DB->next_record();
// test $HideDNU first as it may have been passed from upload_handle
if (!$HideDNU)
    $HideDNU = check_perms('torrents_hide_dnu') || !$NewDNU;
?>

<script type="text/javascript">//<![CDATA[
    function change_tagtext() {
        var tags = new Array();
<?
foreach ($NewCategories as $cat) {
    echo 'tags[' . $cat['id'] . ']="' . $cat['tag'] . '"' . ";\n";
}
?>
        if ($('#category').raw().value == 0) {
            $('#tagtext').html("");
        } else {
            $('#tagtext').html("<strong>The tag "+tags[$('#category').raw().value]+" will be added automatically.</strong>");
        }
    }
<?
if (!empty($Properties))
    echo "addDOMLoadEvent(SynchInterface);";
?>
//]]></script>

<div class="thin">
    <h2>Upload torrent</h2>
    <div class="head">Do not upload from the following list</div>
    <div class="box pad">
        <span style="float:right;clear:right">
            <p><?= $NewDNU ? '<strong class="important_text">' : '' ?>Last Updated: <?= time_diff($Updated) ?><?= $NewDNU ? '</strong>' : '' ?></p>
        </span>
        
        <p>The following releases are currently forbidden from being uploaded to the site. Make sure you have read the list.
            <? if ($HideDNU) { ?>
                <span id="showdnu"><a href="#" onclick="$('#dnulist').toggle(); this.innerHTML=(this.innerHTML=='(Hide)'?'(Show)':'(Hide)'); return false;">(Show)</a></span>
<? } ?>
        </p>
        <table id="dnulist" class="<?= ($HideDNU ? 'hidden' : '') ?>" style="">
            <tr class="colhead_dark">
                <td width="50%"><strong>Name</strong></td>
                <td><strong>Comment</strong></td>
            </tr>
            <?
            foreach ($DNU as $BadUpload) {
                list($Name, $Comment, $Updated) = $BadUpload;
                ?>		
                <tr>
                    <td><?= $Text->full_format($Name) ?></td>
                    <td><?= $Text->full_format($Comment) ?></td>
                </tr>
    <? } ?>
        </table>
    </div>
    <?
    /* -------  Draw a box with imagehost whitelist  ------- */
    $Whitelist = $Cache->get_value('imagehost_whitelist');
    if ($Whitelist === FALSE) {
        $DB->query("SELECT 
                    Imagehost, 
                    Link,
                    Comment,
                    Time,
                    Hidden
                    FROM imagehost_whitelist
                    WHERE Hidden='0'
                    ORDER BY Time DESC");
        $Whitelist = $DB->to_array();
        $Cache->cache_value('imagehost_whitelist', $Whitelist);
    }
    $DB->query("SELECT MAX(iw.Time), IF(MAX(t.Time) < MAX(iw.Time) OR MAX(t.Time) IS NULL,1,0) 
                  FROM imagehost_whitelist as iw
             LEFT JOIN torrents AS t ON t.UserID = '$LoggedUser[ID]' ");
    list($Updated, $NewWL) = $DB->next_record();
// test $HideWL first as it may have been passed from upload_handle
    if (!$HideWL)
        $HideWL = check_perms('torrents_hide_imagehosts') || !$NewWL;
    ?>
    <div class="head">Approved Imagehosts</div>
    <div class="box pad">
        <span style="float:right;clear:right"><p><?=$NewWL ? '<strong class="important_text">' : '' ?>Last Updated: <?= time_diff($Updated) ?><?= $NewWL ? '</strong>' : '' ?></p></span>
        
        <p>You must use one of the following approved imagehosts for all images. 
<? if ($HideWL) { ?>
                <span><a href="#" onclick="$('#whitelist').toggle(); this.innerHTML=(this.innerHTML=='(Hide)'?'(Show)':'(Hide)'); return false;">(Show)</a></span>
<? } ?>
        </p>
        <table id="whitelist" class="<?= ($HideWL ? 'hidden' : '') ?>" style="">
            <tr class="colhead_dark">
                <td width="50%"><strong>Imagehost</strong></td>
                <td><strong>Comment</strong></td>
            </tr>
<?
foreach ($Whitelist as $ImageHost) {
    list($Host, $Link, $Comment, $Updated) = $ImageHost;
    ?>		
                <tr>
                    <td><?=$Text->full_format($Host)?>
    <?
    // if a goto link is supplied and is a validly formed url make a link icon for it
    if (!empty($Link) && $Text->valid_url($Link)) {
        ?><a href="<?= $Link ?>"  target="_blank"><img src="<?=STATIC_SERVER?>common/symbols/offsite.gif" width="16" height="16" style="" alt="Goto <?= $Host ?>" /></a>
    <? } // endif has a link to imagehost  ?>
                    </td>
                    <td><?=$Text->full_format($Comment)?></td>
                </tr>
    <? } ?>
        </table> 
    </div>
<?
    if (check_perms('use_templates')) {
        //include(SERVER_ROOT.'/sections/upload/functions.php'); 
        $Templates = get_templates($LoggedUser['ID']);
        
        $CanDelAny = check_perms('delete_any_template')?'1':'0';
?>
        <div class="box pad shadow">
            <form action="" enctype="multipart/form-data"  method="post" onsubmit="return ($('#template').raw().value!=0);">
                <div style="margin:10px 10%;display:inline">
                    <label for="template">select template: </label>
                    <div id="template_container" style="display: inline-block">
                    <select id="template" name="template" onchange="SelectTemplate(<?=$CanDelAny?>);" title="Select a template (*=public)">
                        <option value="0">---</option>
    <? foreach ($Templates as $template) {
        list($tID, $tName,$tPublic,$tAuthorname) = $template; 
        if ($tPublic==1) $tName .= " (by $tAuthorname)*"
        ?>
                            <option value="<?=$tID?>"<? if($TemplateID==$tID) echo ' selected="selected"' ?>><?=$tName?></option>
    <? } ?>
                    </select>
                    </div>
                    <input type="submit" name="fill" id="fill" value="fill from" disabled="disabled" title="Fill the upload form from selected template" />
                    <!--<input type="submit" name="delete" id="delete" value="delete" disabled="disabled" title="Delete selected template" />-->
                    <input type="button" onclick="DeleteTemplate(<?=$CanDelAny?>);" name="delete" id="delete" value="delete" disabled="disabled" title="Delete selected template" />
                    <input type="button" onclick="OverwriteTemplate(<?=$CanDelAny?>,<?=$tPublic?>);" name="save" id="save" value="save over" disabled="disabled" title="Save current form as selected template (overwrites data in this template)" />
                </div>
                <div style="margin:10px 15% 10px 0;display:inline">
                    
<?          if (check_perms('make_private_templates')) {   
                    $addsep=true; ?>
                    <a href="#" onclick="AddTemplate(<?=$CanDelAny?>,0);" title="Make a private template from the details currently in the form">Add Private Template</a>  
<?          } 
            if (check_perms('make_public_templates')) {   
                 if ($addsep) echo "&nbsp;&nbsp;&nbsp|&nbsp;&nbsp;&nbsp;";  ?>
                    <a href="#" onclick="AddTemplate(<?=$CanDelAny?>,1);" title="Make a public template from the details currently in the form">Add Public Template</a>
<?          }   ?>
                </div> 
            </form>
        </div>
        <script type="text/javascript">//<![CDATA[
        function SynchTemplates(){
            SelectTemplate(<?=$CanDelAny?>);
        }
            addDOMLoadEvent(SynchTemplates);
        //]]></script>
<?
    }
    
?>
    <a id="startform"></a>
<?

/* -------  Draw upload torrent form  ------- */
$TorrentForm->head();
$TorrentForm->simple_form($GenreTags);
$TorrentForm->foot(); 
?>
</div>
<?
show_footer();
?>
