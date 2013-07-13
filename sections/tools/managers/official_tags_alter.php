<?php

enforce_login();
authorize();

if (!check_perms('site_manage_tags')) {
    error(403);
}
include(SERVER_ROOT . '/sections/torrents/functions.php');

$Message = '';
if (isset($_POST['doit'])) {

    if (isset($_POST['oldtags'])) {
        $OldTagIDs = $_POST['oldtags'];
        $ChangeNames = array();
        $NotChangeNames = array();
        $ChangeIDs = array();
        
        foreach ($OldTagIDs AS $OldTagID) {
            if (!is_number($OldTagID)) {
                error(403);
            }
            $DB->query("SELECT Name, Count(ts.ID)
                          FROM tags AS t 
                     LEFT JOIN tag_synomyns AS ts ON ts.TagID=t.ID
                         WHERE t.ID = $OldTagID
                      GROUP BY t.ID
                          ");
            list($SynName, $NumSynomyns) = $DB->next_record();
            if ($NumSynomyns==0){
                $ChangeIDs[] = (int)$OldTagID;
                $ChangeNames[] = $SynName;
            } else
                $NotChangeNames[] = $SynName;
        }
        if(count($NotChangeNames)>0){
            $Message .= "Cannot remove tags from official list that have synonyms: ". implode(', ', $NotChangeNames).". ";
            $Result = 0;
        }
        if(count($ChangeIDs)>0){
            $ChangeIDs = implode(', ', $ChangeIDs); 
            $DB->query("UPDATE tags SET TagType = 'other' WHERE ID IN ($ChangeIDs)"); 
            $Message .= "Removed tags from official list: ". implode(', ', $ChangeNames);
            $Result = 1;
        }
    }

    if ($_POST['newtag']) {
        $Tag = trim($Tag,'.'); // trim dots from the beginning and end
        $Tag = sanitize_tag($_POST['newtag']);
        $TagName = get_tag_synonym($Tag);

        if ($Tag != $TagName) // this was a synonym replacement
            $Message .= "$Tag = $TagName. ";

        $DB->query("SELECT t.ID FROM tags AS t WHERE t.Name LIKE '" . $TagName . "'");
        list($TagID) = $DB->next_record();

        if ($TagID) {
            $DB->query("UPDATE tags SET TagType = 'genre' WHERE ID = $TagID");
        } else { // Tag doesn't exist yet - create tag
            $DB->query("INSERT INTO tags (Name, UserID, TagType, Uses) 
                VALUES ('" . $TagName . "', " . $LoggedUser['ID'] . ", 'genre', 0)");
            $TagID = $DB->inserted_id();
            $Message .= "Created $TagName. ";
        }
        $Message .= "Added $TagName to official list.";
        $Result = 1;
    }
    $Cache->delete_value('genre_tags');
}



// ==============================  super delete ========================

if (isset($_POST['deletetagperm'])) {
    
    if (!check_perms('site_convert_tags')) error(403);
    
    $Result = 0;
    $TagID = (int)$_POST['permdeletetagid'];
 
    $DB->query("SELECT Name, Count(ts.ID)
                          FROM tags AS t 
                     LEFT JOIN tag_synomyns AS ts ON ts.TagID=t.ID
                         WHERE t.ID = $TagID
                      GROUP BY t.ID ");
    list($TagName, $NumSynomyns) = $DB->next_record();
    if ($NumSynomyns>0) {
        $Message .= "Cannot delete a tag that has synonyms: $TagName\n";
        $TagName = '';
    }
    
    if ($TagName) {
         // get all the torrents that have this tag
        $DB->query("SELECT GroupID FROM torrents_tags WHERE TagID='$TagID'"); 
        $GroupIDs = $DB->collect('GroupID');
 
        // remove old entries for tagID
        $DB->query("DELETE FROM torrents_tags_votes WHERE TagID = '$TagID'"); 
        $DB->query("DELETE FROM torrents_tags WHERE TagID = '$TagID'"); 
        $DB->query("DELETE FROM tags WHERE ID = '$TagID'");

        foreach ($GroupIDs as $GID) {
            update_hash($GID); // update tags sphinx delta
        }
        
        
         // get all the requests that have this tag
        $DB->query("SELECT RequestID FROM requests_tags WHERE TagID='$TagID'"); 
        $RequestIDs = $DB->collect('RequestID');
        
        // remove old entries for tagID
        $DB->query("DELETE FROM requests_tags WHERE TagID = '$TagID'");

        foreach ($RequestIDs as $RID) {
            // update_sphinx_requests($RID); // update sphinx requests delta
            $Cache->delete_value('request_'.$RID);
        }
        
        $Message .= "Permanently deleted tag $TagName.";
        $count=count($GroupIDs);
        if ($count > 0) $Message .= " $count torrent taglists updated. ";
        
        //  log action  
        $AllGroupIDs = implode(',', $GroupIDs);
        write_log("Tag $TagName deleted permanently, $count tag-torrent links updated torrents $AllGroupIDs by " . $LoggedUser['Username']);
        $Result = 1;
    }
}



if(isset($_POST['recountall'])) {
 
    if (!check_perms('site_convert_tags'))  error(403);
    
    // this may take a while...
    
    // delete any orphaned torrent-tag links where the torrent no longer exists
    $DB->query("DELETE t 
                  FROM torrents_tags AS t 
             LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID
                 WHERE tg.ID is NULL");
    $numtt =$DB->affected_rows();
    // delete any orphaned torrent-tag-vote links where the torrent no longer exists
    $DB->query("DELETE tv 
                  FROM torrents_tags_votes AS tv 
             LEFT JOIN torrents_group AS tg ON tv.GroupID=tg.ID
                 WHERE tg.ID is NULL");
    $numtv =$DB->affected_rows();
            
    // update tag uses per tag
    $DB->query("UPDATE tags AS t LEFT JOIN 
                (
                    SELECT TagID, COUNT(GroupID) AS TagCount
                      FROM torrents_tags 
                     GROUP BY TagID
                ) AS c 
                ON t.ID = c.TagID
                SET t.Uses=c.TagCount ");
                         
    $numt =$DB->affected_rows();
    $Result = $numtt >= 0 && $numtv >= 0 && $numt >= 0? 1 :0; // just check no sql errors returned - 0 results are not errors
    $Message .= "Recounted total uses for $numt tags. Removed orphans: $numtt tor-tag links, $numtv tag-votes" ;
    
}

/*
// ======================================  del synomyn

if (isset($_POST['delsynomyns'])) {

    if (isset($_POST['oldsyns'])) {
        $OldSynomyns = $_POST['oldsyns'];
        $DeleteCache = array();
        foreach ($OldSynomyns AS $OldSynID) {
            if (!is_number($OldSynID)) {
                error(403);
            }
            $DB->query("SELECT Synomyn FROM tag_synomyns WHERE ID = $OldSynID");
            list($SynName) = $DB->next_record();
            if ($SynName)
                $DeleteCache[] = $SynName;
        }
        $OldSynomyns = implode(', ', $OldSynomyns);
        $DB->query("DELETE FROM tag_synomyns WHERE ID IN ($OldSynomyns)");
        $Cache->delete_value('all_synomyns');
        $Message .= "Deleted synonyms: " . implode(', ', $DeleteCache);
        $Result = 1;
    }
}




// ======================================  convert/add tag to/as synomyn

if (isset($_POST['tagtosynomyn'])) {

    $ParentTagID = (int)$_POST['parenttagid'];
    if ($ParentTagID) {
        $DB->query("SELECT Name FROM tags WHERE ID=$ParentTagID");
        list($ParentTagName) = $DB->next_record();
    }
    
    if (isset($_POST['multi'])) {
        $anchor = "#convertbox";
        $TagsID = explode(",", $_POST['multiID']) ;
        foreach ($TagsID AS $TagID) {
            if (!is_number($TagID)) error(0); 
        }
    } else {
        $TagsID = array( (int)$_POST['movetagid'] );
    }
    
    foreach( $TagsID as $TagID) {
        //$TagID = (int) $_POST['movetagid'];
        //$ParentTagID = (int) $_POST['parenttagid'];
        $TagID = (int)$TagID;
        if ($TagID) {
            //$DB->query("SELECT Name FROM tags WHERE ID=$TagID");
            //list($TagName) = $DB->next_record();
            $DB->query("SELECT Name, Count(ts.ID)
                          FROM tags AS t 
                     LEFT JOIN tag_synomyns AS ts ON ts.TagID=t.ID
                         WHERE t.ID = $TagID
                      GROUP BY t.ID ");
            list($TagName, $NumSynomyns) = $DB->next_record();
            if ($NumSynomyns>0) {
                $Message .= "Cannot remove tags from official list that have synonyms: $TagName\n";
                $TagName = '';
            }
        }

        if ($TagName && $ParentTagName) {

            // check this synonym is not already in syn table 
            $DB->query("SELECT ID FROM tag_synomyns WHERE Synomyn LIKE '" . $TagName . "'");
            list($SynID) = $DB->next_record();

            if ($SynID) {
                $Message .= "$TagName already exists as a synonym for " . get_tag_synonym($TagName);
                $Result = 0;
            } else {

                $DB->query("INSERT INTO tag_synomyns (Synomyn, TagID, UserID) 
                                                     VALUES ('" . $TagName . "', " . $ParentTagID . ", " . $LoggedUser['ID'] . " )");
                $Cache->delete_value('all_synomyns');
                $Result = 1;
                // if we are just adding a tag as a synomyn and not converting there is nothing more to do

                if (isset($_POST['converttag'])) {
                    // convert a synomyn to a tag properly
                    if (!check_perms('site_convert_tags')) {
                        $Message .= "(You do not have permission to convert an exisiting tag) Added tag $TagName as synonym for $ParentTagName";
                    } else {
                        // 'convert refrences to the original tag to parenttag and cleanup db 
             
                        $DB->query("SELECT tt.GroupID, tt.PositiveVotes, tt.NegativeVotes, Count(tt2.TagID) AS Count
                                                      FROM torrents_tags AS tt
                                                 LEFT JOIN torrents_tags AS tt2 ON tt2.GroupID=tt.GroupID
                                                            AND tt2.TagID=$ParentTagID
                                                     WHERE tt.TagID=$TagID  
                                                  GROUP BY tt.GroupID");
                        
                        $GroupInfos = $DB->to_array(false, MYSQLI_BOTH);
                        //$Message .= " count groupinfos=".count($GroupInfos) . "  ";
                        $NumAffectedTorrents = count($GroupInfos);
                        $NumChangedFilelists = 0;
                        if ($NumAffectedTorrents > 0) {
                            //$SQL = 'INSERT IGNORE INTO torrents_tags 
                            //                      (TagID, GroupID, PositiveVotes, NegativeVotes, UserID) VALUES';
                            $SQL='';
                            $Div = ''; $Div2 = '';
                            $MsgGroups = "torrents ";
                            foreach ($GroupInfos as $Group) {
                                list($GroupID, $PVotes, $NVotes, $Count) = $Group;
                                if ($Count==0){ // only insert parenttag into groups where not already present
                                    $SQL .= "$Div ('$ParentTagID', '$GroupID', '$PVotes', '$NVotes', '{$LoggedUser['ID']}')";
                                    $Div = ',';
                                    $NumChangedFilelists++;
                                }
                                $MsgGroups .= "$Div2$GroupID";
                                $Div2 = ',';
                  
                            }
                        
                            // update torrents_tags with entries for parentTagID
                            if($SQL !=''){
                                $SQL = "INSERT IGNORE INTO torrents_tags 
                                                  (TagID, GroupID, PositiveVotes, NegativeVotes, UserID) VALUES $SQL";
                                $DB->query($SQL);
                            }
                            // update the Uses where parenttag has been added as a replacement for tag
                            if($NumChangedFilelists>0)
                                $DB->query("UPDATE tags SET Uses=(Uses+$NumChangedFilelists) WHERE ID='$ParentTagID'");
                            
                            $DB->query("DELETE FROM torrents_tags WHERE TagID = '$TagID'");
                        }
                        //// remove old entries for tagID
                        $DB->query("DELETE FROM tags WHERE ID = '$TagID'");

                        foreach ($GroupInfos as $Group) {
                            update_hash($Group[0]);
                        }
                        $Message .= "Converted tag $TagName to synonym for $ParentTagName. ";
                        // probably we should log this action in some way
                        write_log("Tag $TagName converted to synonym for tag $ParentTagName, $NumAffectedTorrents tag-torrent links updated $MsgGroups by " . $LoggedUser['Username']);
                    
                    }
                } else {
                    $Message .= "Added tag $TagName as synonym for $ParentTagName";
                }
            }
        }
    }
}


// ======================================  add synomyn

if (isset($_POST['addsynomyn'])) {

    $ParentTagID = (int) $_POST['parenttagid'];

    if (isset($_POST['newsynname']) && $ParentTagID) {
 
        $TagName = sanitize_tag(trim($_POST['newsynname'],'.'));
        if ($TagName != '') {
            // check this synonym is not already in syn table or tag table
            $DB->query("SELECT ID FROM tag_synomyns WHERE Synomyn LIKE '" . $TagName . "'");
            list($SynID) = $DB->next_record();
            if ($SynID) {
                $Message .= "$TagName already exists as a synonym for " . get_tag_synonym($TagName);
                $Result = 0;
            } else {
                $DB->query("SELECT ID FROM tags WHERE Name LIKE '" . $TagName . "'");
                list($SynID) = $DB->next_record();
                if ($SynID) {
                    $Message .= "Cannot add $TagName as a synonym - already exists as a tag.";
                    $Result = 0;
                } else { // synonym doesn't exist yet - create
                    $DB->query("INSERT INTO tag_synomyns (Synomyn, TagID, UserID) 
                                                        VALUES ('" . $TagName . "', " . $ParentTagID . ", " . $LoggedUser['ID'] . " )");
                    $Cache->delete_value('all_synomyns');
                    $Result = 1;
                    $Message .= "$TagName created as a synonym for " . get_tag_synonym($TagName);
                }
            }
        }
    }
}

*/


if ($Message != '') {
    header("Location: tools.php?action=official_tags&rst=$Result&msg=" . htmlentities($Message) .$anchor);
} else {
    header('Location: tools.php?action=official_tags'.$anchor);
}
?>
