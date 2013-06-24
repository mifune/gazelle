<?php

$Includes = array('all','own','other');
$Orders = array('None', 'TagID', 'TagName', 'AdderID', 'AddedBy', 'IsAdder', 'IsOwner', 'TorrentID', 'TorrentName','UploaderID','Uploader','Votes','Way');
$Ways = array('ASC'=>'Ascending', 'DESC'=>'Descending');

if (isset($_GET['userid'])) $UserID = $_GET['userid'];
else $UserID = $LoggedUser['ID'];

if(!is_number($UserID)) { error(0); }

$User = user_info($UserID);
$Perms = get_permissions($User['PermissionID']);
$UserClass = $Perms['Class'];


if(!check_paranoia('tags', $User['Paranoia'], $UserClass, $UserID)) { error(PARANOIA_MSG); }



function header_link($SortKey,$DefaultWay="DESC") {
	global $Order,$Way,$Document;
	if($SortKey==$Order) {
		if($Way=="DESC") { $NewWay="ASC"; }
		else { $NewWay="DESC"; }
	} else { $NewWay=$DefaultWay; }
	
	return "$Document.php?way=".$NewWay."&amp;order=".$SortKey."&amp;".get_url(array('way','order'));
}





if(!empty($_GET['include']) && in_array($_GET['include'], $Includes)) {
	$Include = $_GET['include'];
} else {
	$Include = 'all';
}

if ($Include == 'own') $AND_WHERE = " AND t.UserID = '$UserID'";
elseif ($Include == 'other') $AND_WHERE = " AND t.UserID != '$UserID'";
else  $AND_WHERE = '';




if (isset($LoggedUser['TorrentsPerPage'])) {
	$TorrentsPerPage = $LoggedUser['TorrentsPerPage'];
} else {
	$TorrentsPerPage = TORRENTS_PER_PAGE;
}

if(!empty($_GET['page']) && is_number($_GET['page'])) {
	$Page = $_GET['page'];
	$Limit = ($Page-1)*$TorrentsPerPage.', '.$TorrentsPerPage;
} else {
	$Page = 1;
	$Limit = $TorrentsPerPage;
}

if(!empty($_GET['order']) && in_array($_GET['order'], $Orders) ) {
	$Order = $_GET['order'];
} else {
	$Order = 'None';
    $_GET['order'] = $Order;
}

if(!empty($_GET['way']) && array_key_exists($_GET['way'], $Ways)) {
	$Way = $_GET['way'];
} else {
	$Way = 'DESC';
}

if ($Order == 'None') $ORDERBY = '';
else $ORDERBY = " ORDER BY $Order $Way ";


    
if ($_GET['type']=='votes') {
    $TitleEnd = "'s voted on tags";
} else {
    $TitleEnd = "'s added tags";
}

show_header( "$User[Username]$TitleEnd" );
        
?>


<div class="thin">
	<h2><?=format_username($UserID, $User['Username']).$TitleEnd;?></h2>
	<div class="linkbox">
 
        [<a href="userhistory.php?action=tag_history&amp;type=added&amp;<?=get_url(array('action','type','page'))?>" title="View <?=$User['Username']?>'s added tags">Tags added</a>] 
  &nbsp;[<a href="userhistory.php?action=tag_history&amp;type=votes&amp;<?=get_url(array('action','type','page'))?>" title="View <?=$User['Username']?>'s added tags">Tags voted on</a>] 
    </div>

    <div class="head">options</div>
    <table class="noborder">
        <tr>
            <td class="right" title="show all tags">show all tags 
                <input type="radio" name="include" onchange="location.href='userhistory.php?action=tag_history&amp;include=all&amp;<?=get_url(array('action','include','page'))?>'" value="all" <?if($Include=='all')echo'checked="checked"';?> />
            </td>
            <td class="center" title="show only tags on other users torrents">show only tags on others torrents 
                <input type="radio" name="include" onchange="location.href='userhistory.php?action=tag_history&amp;include=other&amp;<?=get_url(array('action','include','page'))?>'" value="other" <?if($Include=='other')echo'checked="checked"';?>/>
            </td>
            <td class="center" title="show only tags on <?=$User['Username']?>'s torrents">show only tags on own torrents
                <input type="radio" name="include" onchange="location.href='userhistory.php?action=tag_history&amp;include=own&amp;<?=get_url(array('action','include','page'))?>'" value="own" <?if($Include=='own')echo'checked="checked"';?>/>
            </td>
        </tr>
    </table>
    <br/>
<?
    if($_GET['type']=='votes') {

        $DB->query("SELECT SQL_CALC_FOUND_ROWS 
                            tags.ID AS TagID, tags.Name AS TagName, tt.UserID As AdderID, um1.Username AS AddedBy, 
                            IF(tt.UserID = '$UserID',1,0) AS IsAdder, IF(t.UserID = '$UserID',1,0) AS IsOwner, 
                            tg.ID AS TorrentID, tg.Name AS TorrentName, um2.ID AS UploaderID, um2.Username AS Uploader,
                            ( tt.PositiveVotes- tt.NegativeVotes) AS Votes, ttv.Way 
                      FROM torrents_tags_votes AS ttv 
                      JOIN tags ON ttv.TagID=tags.ID 
                      JOIN torrents_tags AS tt ON tt.TagID=ttv.TagID AND tt.GroupID=ttv.GroupID
                      JOIN torrents AS t ON t.GroupID=ttv.GroupID 
                      JOIN torrents_group AS tg ON t.GroupID=tg.ID 
                 LEFT JOIN users_main AS um1 ON um1.ID=tt.UserID
                 LEFT JOIN users_main AS um2 ON um2.ID=t.UserID
                     WHERE ttv.UserID = '$UserID'
                        $AND_WHERE
                        $ORDERBY
                        LIMIT $Limit"); 
    } else {
        $DB->query("SELECT SQL_CALC_FOUND_ROWS 
                            tags.ID AS TagID, tags.Name AS TagName, tt.UserID As AdderID, um1.Username AS AddedBy, 
                            IF(tt.UserID = '$UserID',1,0) AS IsAdder, IF(t.UserID = '$UserID',1,0) AS IsOwner, 
                            tg.ID AS TorrentID, tg.Name AS TorrentName, um2.ID AS UploaderID, um2.Username AS Uploader,
                            ( tt.PositiveVotes- tt.NegativeVotes) AS Votes, ttv.Way 
                      FROM torrents_tags AS tt 
                      JOIN tags ON tt.TagID=tags.ID 
                      JOIN torrents AS t ON t.GroupID=tt.GroupID 
                      JOIN torrents_group AS tg ON tt.GroupID=tg.ID 
                 LEFT JOIN users_main AS um1 ON um1.ID=tt.UserID
                 LEFT JOIN users_main AS um2 ON um2.ID=t.UserID
                 LEFT JOIN torrents_tags_votes AS ttv ON ttv.TagID=tags.ID AND ttv.GroupID=tt.GroupID AND ttv.UserID='$UserID'
                     WHERE tt.UserID = '$UserID'
                        $AND_WHERE
                        $ORDERBY
                        LIMIT $Limit");
    }
    
    $Tags = $DB->to_array(false, MYSQLI_NUM);

    $DB->query("SELECT FOUND_ROWS()");
    list($TagCount) = $DB->next_record();
    
    $Pages=get_pages($Page,$TagCount,$TorrentsPerPage,8);
    
?>
	<div class="linkbox"><?=$Pages?></div>
    <div class="head"><?=$TagCount?> tags</div>
    <table>
        <tr class="colhead">
            <td>Tag <span style="margin-left:60px">
                    <a href="<?=header_link('TagID')?>" title="sort by tag ID">(id)</a> <a href="<?=header_link('TagName', 'ASC')?>" title="sort by Tag Name">(az)</a>
                </span>
            </td>
            <td class="right"><a href="<?=header_link('IsAdder')?>" title="sort by is tag adder">adder</a></td>
            <td><a href="<?=header_link('AddedBy')?>" title="sort by added by">Added By</a></td>
            <td><a href="<?=header_link('Way')?>" title="sort by vote direction">Way</a></td>
            <td><a href="<?=header_link('Votes')?>" title="sort by number of votes">Votes</a></td>
            <td>Torrent <span style="margin-left:60px">
                    <a href="<?=header_link('TorrentID')?>" title="sort by torrent ID">(id)</a> <a href="<?=header_link('TorrentName', 'ASC')?>" title="sort by torrent name">(az)</a>
                </span>
            </td>
            <td class="right"><a href="<?=header_link('IsOwner')?>" title="sort by is torrent owner">owner</a></td>
            <td><a href="<?=header_link('Uploader')?>" title="sort by uploader">Uploader</a></td>
        </tr>
<?
    foreach ($Tags as $TagInfo) {
        list($TagID, $TagName, $AdderID, $AddedBy, $IsAdder, $IsOwner, $GroupID, $TorrentName, $UploaderID, $Uploader, $Votes, $Way) = $TagInfo;
        $row = $row== 'a'? 'b':'a';
?>
        <tr class="row<?=$row?>">
            <td><a href="torrents.php?taglist=<?=$TagName?>"><?=$TagName?></a></td>
            <td class="right"><?if($IsAdder)echo'<img src="static/common/symbols/tick.png" title="tag was added by '.$AddedBy.'" />'; ?></td>
            <td><a href="user.php?id=<?=$AdderID?>" title="tag was added by <?=$AddedBy?>"><?=$AddedBy?></a></td>
            <td>
<?              if (!$Way) echo '<span class="blue">-</span>';
                elseif ($Way=='up') echo '<span class="green">Up</span>';
                else echo '<span class="red">Down</span>';?>
            </td>
            <td><?=$Votes?></td>
            <td><a href="torrents.php?id=<?=$GroupID?>" title="<?=$TorrentName?>"><?=cut_string($TorrentName,50)?></a></td>
            <td class="right"><?
                if($IsOwner)echo'<img src="static/common/symbols/tick.png" title="torrent was uploaded by '.$Uploader.'" />';?></td>
            <td><a href="user.php?id=<?=$UploaderID?>" title="torrent was uploaded by <?=$Uploader?>"><?=$Uploader?></a></td>
        </tr>
        
<?  }    ?>
    </table>
	<div class="linkbox"><?=$Pages?></div>
 
</div>

<?
show_footer();
?>
