<?


?>


<div class="head">Possible dupes</div>
<table class="torrent_table grouping" id="torrent_table">
    <tr class="colhead">
        <td class="small cats_col"></td>
        <td width="100%">Name</td>
        <td>Files</td>
        <td><a href="<?= header_link('time') ?>">Time</a></td>
        <td><a href="<?= header_link('size') ?>">Size</a></td>
        <td class="sign"><a href="<?= header_link('snatched') ?>"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/snatched.png" alt="Snatches" title="Snatches" /></a></td>
        <td class="sign"><a href="<?= header_link('seeders') ?>"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/seeders.png" alt="Seeders" title="Seeders" /></a></td>
        <td class="sign"><a href="<?= header_link('leechers') ?>"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/leechers.png" alt="Leechers" title="Leechers" /></a></td>
        <td>Uploader</td>
    </tr>
    <?
    // Start printing torrent list
    $row='a';
    $lastday = 0;
    foreach ($DupeResults as $GroupID => $GData) {   
        list($GroupID2, $GroupName, $TagList, $Torrents, $FreeTorrent, $Image, $TotalLeechers, 
                $NewCategoryID, $SearchText, $TotalSeeders, $MaxSize, $TotalSnatched, $GroupTime) = array_values($GData);

        list($TorrentID, $Data) = each($Torrents);
         
        
        $TagList = explode(' ', str_replace('_', '.', $TagList));

        $TorrentTags = array();
        $numtags=0;
		foreach($TagList as $Tag) {
            if ($numtags++>=$LoggedUser['MaxTags'])  break;
            $TorrentTags[] = '<a href="torrents.php?' . $Action . '&amp;taglist=' . $Tag . '">' . $Tag . '</a>';
        }
        $TorrentTags = implode(' ', $TorrentTags);

 
        //$AddExtra = torrent_icons($Data, $TorrentID, $Data['Status'], in_array($GroupID, $Bookmarks));
            
        $row = ($row == 'a'? 'b' : 'a');
        $IsMarkedForDeletion = $Data['Status'] == 'Warned' || $Data['Status'] == 'Pending';
         
        ?> 
        <tr class="torrent <?=($IsMarkedForDeletion?'redbar':"row$row")?>">
            <td class="center cats_col">
                <? $CatImg = 'static/common/caticons/' . $NewCategories[$NewCategoryID]['image']; ?>
                <div title="<?= $NewCategories[$NewCategoryID]['tag'] ?>"><a href="torrents.php?filter_cat[<?=$NewCategoryID?>]=1"><img src="<?= $CatImg ?>" /></a></div>
            </td>
            <td>

<?              if (check_perms('torrents_review') && $Data['Status'] == 'Okay') { 
                    echo  '&nbsp;'.get_status_icon('Okay');
                }
                if ($Data['ReportCount'] > 0) {
                    $Title = "This torrent has ".$Data['ReportCount']." active ".($Data['ReportCount'] > 1 ?'reports' : 'report');
                    $GroupName .= ' /<span class="reported" title="'.$Title.'"> Reported</span>';
                }
                
                ?>
                    <?=$AddExtra?> <a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a> 
 
                    <?=$AddExtra?>
                    <a href="torrents.php?id=<?=$GroupID?>"><?=$GroupName?></a> 
             
                <br />
                <? if ($LoggedUser['HideTagsInLists'] !== 1) { ?>
                <div class="tags">
                   <?= $TorrentTags ?>
                </div>
                <? } ?>
            </td>
            <td class="center"><?=number_format($Data['FileCount'])?></td>
            <td class="nobr"><?=time_diff($Data['Time'], 1) ?></td>
            <td class="nobr"><?= get_size($Data['Size']) ?></td>
            <td><?= number_format($Data['Snatched']) ?></td>
            <td<?= ($Data['Seeders'] == 0) ? ' class="r00"' : '' ?>><?= number_format($Data['Seeders']) ?></td>
            <td><?= number_format($Data['Leechers']) ?></td>
            <td class="user"><a href="user.php?id=<?= $Data['UserID'] ?>" class="user"><?= $Data['Username'] ?></a></td>
        </tr>
        <?
    }
    ?>
</table>
 
