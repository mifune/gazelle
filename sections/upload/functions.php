<?php


function get_templates($UserID) {
    global $DB, $Cache;
    
    $UserTemplates = $Cache->get_value('templates_ids_' . $UserID);
    if ($UserTemplates === FALSE) {
                        $DB->query("SELECT 
                                    t.ID,
                                    t.Name,
                                    t.Public,
                                    u.Username
                               FROM upload_templates as t
                          LEFT JOIN users_main AS u ON u.ID=t.UserID
                              WHERE t.UserID='$UserID' 
                                AND Public='0'
                           ORDER BY Name");
                        $UserTemplates = $DB->to_array();
                        $Cache->cache_value('templates_ids_' . $UserID, $UserTemplates, 96400);
    }
    $PublicTemplates = $Cache->get_value('templates_public');
    if ($PublicTemplates === FALSE) {
                        $DB->query("SELECT 
                                    t.ID,
                                    t.Name,
                                    t.Public,
                                    u.Username
                               FROM upload_templates as t
                          LEFT JOIN users_main AS u ON u.ID=t.UserID
                              WHERE Public='1'
                           ORDER BY Name");
                        $PublicTemplates = $DB->to_array();
                        $Cache->cache_value('templates_public', $PublicTemplates, 96400);
    }
    return array_merge($UserTemplates, $PublicTemplates);
}


/**
 * Returns the inner list elements of the tag table for a torrent
 * (this function calls/rebuilds the group_info cache for the torrent - in theory just a call to memcache as all calls come through the torrent details page)
 * @param int $GroupID The group id of the torrent
 * @return the html for the taglist
 */
function get_templatelist_html($UserID, $SelectedTemplateID =0) {
    global $DB, $Cache;
    
    ob_start();
 
    $Templates = get_templates($UserID);
?>
                    
        <select id="template" name="template" onchange="SelectTemplate(<?=(check_perms('delete_any_template')?'1':'0')?>);" title="Select a template (*=public)">
                <option value="0" <? if($SelectedTemplateID==0) echo ' selected="selected"' ?>>---</option>
    <?  
            foreach ($Templates as $template) {
                list($tID, $tName,$tPublic,$tAuthorname) = $template; 
                if ($tPublic==1) $tName .= " (by $tAuthorname)*"
?>
                <option value="<?=$tID?>"<? if($SelectedTemplateID==$tID) echo ' selected="selected"' ?>><?=$tName?></option>
<?          } ?>
        </select>
<?                
    $html = ob_get_contents(); 
    ob_end_clean();

    return $html;
}



?>
