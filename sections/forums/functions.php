<?php
function update_forum_info($ForumID, $AdjustNumTopics = 0, $BeginEndTransaction = true)
{
    global $DB, $Cache;

    if ($BeginEndTransaction) $Cache->begin_transaction('forums_list');

    $DB->query("SELECT
            t.ID,
            t.LastPostID,
            t.Title,
            p.AuthorID,
            um.Username,
            p.AddedTime,
            (SELECT COUNT(pp.ID) FROM forums_posts AS pp JOIN forums_topics AS tt ON pp.TopicID=tt.ID WHERE tt.ForumID='$ForumID'),
            t.IsLocked,
            t.IsSticky
            FROM forums_topics AS t
            JOIN forums_posts AS p ON p.ID=t.LastPostID
            LEFT JOIN users_main AS um ON um.ID=p.AuthorID
            WHERE t.ForumID='$ForumID'
            GROUP BY t.ID
            ORDER BY p.AddedTime DESC LIMIT 1");
            //ORDER BY t.LastPostID DESC LIMIT 1");
    list($NewLastTopic, $NewLastPostID, $NewLastTitle, $NewLastAuthorID, $NewLastAuthorName, $NewLastAddedTime, $NumPosts, $NewLocked, $NewSticky) = $DB->next_record(MYSQLI_BOTH, false);

    $UpdateArray = array(
            'NumPosts'=>$NumPosts,
            'LastPostID'=>$NewLastPostID,
            'LastPostAuthorID'=>$NewLastAuthorID,
            'Username'=>$NewLastAuthorName,
            'LastPostTopicID'=>$NewLastTopic,
            'LastPostTime'=>$NewLastAddedTime,
            'Title'=>$NewLastTitle,
            'IsLocked'=>$NewLocked,
            'IsSticky'=>$NewSticky
            );

    if ($AdjustNumTopics !=0) { // '-1' or '+1' etc
                $SetNumTopics = "NumTopics=NumTopics$AdjustNumTopics, ";
                $UpdateArray['NumTopics']=$AdjustNumTopics;
    } else $SetNumTopics ='';

    $SQL = "UPDATE forums SET $SetNumTopics
                    NumPosts='$NumPosts',
                    LastPostTopicID='$NewLastTopic',
                    LastPostID='$NewLastPostID',
                    LastPostAuthorID='$NewLastAuthorID',
                    LastPostTime='$NewLastAddedTime'
                    WHERE ID='$ForumID'";

    $DB->query($SQL);

    $Cache->update_row($ForumID, $UpdateArray);
    if ($BeginEndTransaction) $Cache->commit_transaction(0);
}

function get_thread_info($ThreadID, $Return = true, $SelectiveCache = false)
{
    global $DB, $Cache;
    if (!$ThreadInfo = $Cache->get_value('thread_'.$ThreadID.'_info')) {
        $DB->query("SELECT
            t.Title,
            t.ForumID,
            t.IsLocked,
            t.IsSticky,
            COUNT(fp.id) AS Posts,
            t.LastPostAuthorID,
            ISNULL(p.TopicID) AS NoPoll,
            t.StickyPostID
            FROM forums_topics AS t
            JOIN forums_posts AS fp ON fp.TopicID = t.ID
            LEFT JOIN forums_polls AS p ON p.TopicID=t.ID
            WHERE t.ID = '$ThreadID'
            GROUP BY fp.TopicID");
        if ($DB->record_count()==0) { error(404); }
        $ThreadInfo = $DB->next_record(MYSQLI_ASSOC);
        if ($ThreadInfo['StickyPostID']) {
            $ThreadInfo['Posts']--;
            $DB->query("SELECT
                p.ID,
                p.AuthorID,
                p.AddedTime,
                p.Body,
                p.EditedUserID,
                p.EditedTime,
                ed.Username
                FROM forums_posts as p
                LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
                WHERE p.TopicID = '$ThreadID' AND p.ID = '".$ThreadInfo['StickyPostID']."'");
            list($ThreadInfo['StickyPost']) = $DB->to_array(false, MYSQLI_ASSOC);
        }
        if (!$SelectiveCache || !$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
            $Cache->cache_value('thread_'.$ThreadID.'_info', $ThreadInfo, 0);
        }
    }
    if ($Return) {
        return $ThreadInfo;
    }
}

function check_forumperm($ForumID, $Perm = 'Read')
{
    global $LoggedUser, $Forums;
    if ($LoggedUser['CustomForums'][$ForumID] == 1) {
        return true;
    }
    if ($Forums[$ForumID]['MinClass'.$Perm] > $LoggedUser['Class'] && (!isset($LoggedUser['CustomForums'][$ForumID]) || $LoggedUser['CustomForums'][$ForumID] == 0)) {
        return false;
    }
    if (isset($LoggedUser['CustomForums'][$ForumID]) && $LoggedUser['CustomForums'][$ForumID] == 0) {
        return false;
    }

    return true;
}

function update_latest_topics()
{
        global $LoggedUser, $Classes, $Cache, $DB, $ExcludeForums;

        foreach ($Classes as $Class) {
            $Cache->delete_value('latest_topics_'.$Class['ID']);
            $Cache->delete_value('latest_topics_nogames_'.$Class['ID']);
        }

}

function print_forums_select($Forums, $ForumCats, $SelectedForumID=false, $ElementID = '')
{
    global $Cache, $DB, $LoggedUser;
    if ($ElementID) $ElementID = 'id="'.display_str($ElementID).'"';
    else $ElementID ='';
?>
                <select name="forumid" <?=$ElementID?>>
<?php
$OpenGroup = false;
$LastCategoryID=-1;

foreach ($Forums as $Forum) {
  if ( !check_forumperm($Forum['ID'], 'Write')) continue;

    if ($Forum['CategoryID'] != $LastCategoryID) {
        $LastCategoryID = $Forum['CategoryID'];
        if ($OpenGroup) { ?>
                    </optgroup>
<?php		} ?>
                    <optgroup label="<?=$ForumCats[$Forum['CategoryID']]?>">
<?php		$OpenGroup = true;
    }
?>
                        <option value="<?=$Forum['ID']?>"<?php if ($SelectedForumID == $Forum['ID']) { echo ' selected="selected"';} ?>><?=$Forum['Name']?></option>
<?php } ?>
                    </optgroup>
                </select>
<?php
}

function get_forum_cats()
{
    global $Cache, $DB;

    $ForumCats = $Cache->get_value('forums_categories');
    if ($ForumCats === false) {
          $DB->query("SELECT ID, Name FROM forums_categories");
          $ForumCats = array();
          while (list($ID, $Name) =  $DB->next_record()) {
                $ForumCats[$ID] = $Name;
          }
          $Cache->cache_value('forums_categories', $ForumCats, 0); //Inf cache.
    }

    return $ForumCats;
}

function get_forums_info()
{
    global $Cache, $DB;

    //This variable contains all our lovely forum data
    if (!$Forums = $Cache->get_value('forums_list')) {
          $DB->query("SELECT
                f.ID,
                f.CategoryID,
                f.Name,
                f.Description,
                f.MinClassRead,
                f.MinClassWrite,
                f.MinClassCreate,
                f.NumTopics,
                f.NumPosts,
                f.LastPostID,
                f.LastPostAuthorID,
                um.Username,
                f.LastPostTopicID,
                f.LastPostTime,
                COUNT(sr.ThreadID) AS SpecificRules,
                t.Title,
                t.IsLocked,
                t.IsSticky
                FROM forums AS f
                JOIN forums_categories AS fc ON fc.ID = f.CategoryID
                LEFT JOIN forums_topics as t ON t.ID = f.LastPostTopicID
                LEFT JOIN users_main AS um ON um.ID=f.LastPostAuthorID
                LEFT JOIN forums_specific_rules AS sr ON sr.ForumID = f.ID
                GROUP BY f.ID
                ORDER BY fc.Sort, fc.Name, f.CategoryID, f.Sort");
          $Forums = $DB->to_array('ID', MYSQLI_ASSOC, false);
          foreach ($Forums as $ForumID => $Forum) {
                if (count($Forum['SpecificRules'])) {
                      $DB->query("SELECT ThreadID FROM forums_specific_rules WHERE ForumID = ".$ForumID);
                      $ThreadIDs = $DB->collect('ThreadID');
                      $Forums[$ForumID]['SpecificRules'] = $ThreadIDs;
                }
          }
          unset($ForumID, $Forum);
          $Cache->cache_value('forums_list', $Forums, 0); //Inf cache.

    }

    return $Forums;
}

function get_thread_views($ThreadID)
{
    global $Cache, $DB;

    $NumViews = $Cache->get_value('thread_views_'.$ThreadID);
    if ($NumViews===false) {
          $DB->query("SELECT NumViews FROM forums_topics WHERE ID='$ThreadID'");
          list($NumViews) = $DB->next_record();
          if(!$NumViews)$NumViews=0;
          $Cache->cache_value('thread_views_'.$ThreadID, $NumViews, 0); //Inf cache.
    }

    return $NumViews;
}
