<?php
function check_size_dupes($TorrentFilelist, $ExcludeID=0) {
    global $SS, $ExcludeBytesDupeCheck, $Image_FileTypes;

    $SS->limit(0, 10, 10);
    $SS->SetSortMode(SPH_SORT_ATTR_DESC, 'time');
    $SS->set_index(SPHINX_INDEX . ' delta');

    $AllResults=array();
    $UniqueResults = 0;

    foreach ($TorrentFilelist as $File) {
        list($Size, $Name) = $File;

        // skip matching files < 1mb in size
        if ($Size < 1024*1024*2) continue;

        // skip image files
        preg_match('/\.([^\.]+)$/i', $Name, $ext);
        if (in_array($ext[1], $Image_FileTypes)) continue;


        if(isset($ExcludeBytesDupeCheck[$Size])) {
            $FakeEntry = array( array( 'excluded'=> $ExcludeBytesDupeCheck[$Size],
                                       'dupedfileexact'=>$Name,
                                       'dupedfile'=>"$Name (".  get_size($Size).")" ) );
            $AllResults = array_merge($AllResults, $FakeEntry);
            continue;
        }

        $Query = '@filelist "' . $SS->EscapeString($Size) .'"';  // . '"~20';

        $Results = $SS->search($Query, '', 0, array(), '', '');
        $Num = $SS->TotalResults;
        if ($Num>0){
            // These ones were not found in the cache, run SQL
            if (!empty($Results['notfound'])) {

                $SQLResults = get_groups($Results['notfound']);

                if (is_array($SQLResults['notfound'])) { // Something wasn't found in the db, remove it from results
                    reset($SQLResults['notfound']);
                    foreach ($SQLResults['notfound'] as $ID) {
                        unset($SQLResults['matches'][$ID]);
                        unset($Results['matches'][$ID]);
                    }
                }
                // Merge SQL results with sphinx/memcached results
                foreach ($SQLResults['matches'] as $ID => $SQLResult) {
                    $Results['matches'][$ID] = array_merge($Results['matches'][$ID], $SQLResult);
                    ksort($Results['matches'][$ID]);
                }
            }
            foreach ($Results['matches'] as $ID => $tdata) {
                if ( $tdata['ID']==$ExcludeID ) {
                    unset($Results['matches'][$ID]);
                }
                elseif ( (time_ago($tdata['Torrents'][$ID]['Time']) > 24*3600*EXCLUDE_DUPES_AFTER_DAYS) &&
                            ($tdata['Torrents'][$ID]['Seeders']< EXCLUDE_DUPES_SEEDS) ) {
                    unset($Results['matches'][$ID]);
                } else {
                    $Results['matches'][$ID]['dupedfile'] = "$Name (".  get_size($Size).")";
                }
            }
            if (count($Results['matches'])>0) {
                $UniqueResults++;
                $AllResults = array_merge($AllResults, $Results['matches']);

                if (count($AllResults)>=500) break;
            }
        }
    }
    $NumFiles = count($TorrentFilelist);
    if(count($AllResults)<1) return array('UniqueMatches'=>0, 'NumChecked'=>$NumFiles, 'DupeResults'=>false);
    return array('UniqueMatches'=>$UniqueResults, 'NumChecked'=>$NumFiles, 'DupeResults'=>$AllResults) ;
}

function get_templates_private($UserID) {
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
    return $UserTemplates;
}

function get_templates_public() {
    global $DB, $Cache;
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
    return $PublicTemplates;
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

    $TemplatesPrivate = get_templates_private($UserID);
    $TemplatesPublic = get_templates_public();
?>

        <select id="template" name="template" onchange="SelectTemplate(<?=(check_perms('delete_any_template')?'1':'0')?>);" title="Select a template (*=public)">
            <option class="indent" value="0" <?php  if($SelectedTemplateID==0) echo ' selected="selected"' ?>>---</option>
<?php
        if(count($TemplatesPrivate)>0) {
?>
            <optgroup label="private templates">
<?php
            foreach ($TemplatesPrivate as $template) {
                list($tID, $tName,$tPublic,$tAuthorname) = $template;
?>
                <option class="indent" value="<?=$tID?>"<?php  if($SelectedTemplateID==$tID) echo ' selected="selected"' ?>><?=$tName?></option>
<?php           }         ?>
            </optgroup>
<?php
        }
        if(count($TemplatesPublic)>0) {
?>
            <optgroup label="public templates">
<?php
            foreach ($TemplatesPublic as $template) {
                list($tID, $tName,$tPublic,$tAuthorname) = $template;
                if ($tPublic==1) $tName .= " (by $tAuthorname)*"
?>
                <option class="indent" value="<?=$tID?>"<?php  if($SelectedTemplateID==$tID) echo ' selected="selected"' ?>><?=$tName?></option>
<?php           }           ?>
            </optgroup>
<?php       }         ?>
        </select>
<?php
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
}
