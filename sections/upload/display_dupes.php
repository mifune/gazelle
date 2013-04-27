<?

 

if (!$INLINE) {  
    show_header("Dupe check for $DupeTitle");
    ?>
    <div class="thin">
        <h2>Dupe check for <?=$DupeTitle?></h2> 
    <?   
}
?>
    <div class="head">Possible dupes</div>
<?
if (count($DupeResults)<1) {
    ?>
    <div class="box pad">No files with the same bytesize were found in the torrents database</div>
    <?
} else {
    ?>
    <table class="torrent_table grouping" id="torrent_table">
        <tr class="colhead">
            <td class="small cats_col"></td>
            <td width="100%">Name</td>
            <td>Files</td>
            <td>Time</td>
            <td>Size</td>
            <td class="sign"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/snatched.png" alt="Snatches" title="Snatches" /></td>
            <td class="sign"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/seeders.png" alt="Seeders" title="Seeders" /></td>
            <td class="sign"><img src="static/styles/<?= $LoggedUser['StyleName'] ?>/images/leechers.png" alt="Leechers" title="Leechers" /></td>
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

    <?              //if (check_perms('torrents_review') && $Data['Status'] == 'Okay') { 
                    //    echo  '&nbsp;'.get_status_icon('Okay');
                    //}
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
    <?
}

if(!$INLINE) {
    ?>
    </div>
    <?
    show_footer();
}
?>