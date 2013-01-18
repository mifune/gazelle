<?

show_header('Official Tags');
?>
<div class="thin">
    <h2>Official Tags</h2>
    <div class="tagtable center">
        <div>
			<table class="tagtable shadow">
				<tr class="colhead">
					<td style="font-weight: bold">Tag</td>
					<td style="font-weight: bold">Uses</td>
					<td>&nbsp;&nbsp;&nbsp;</td>
					<td style="font-weight: bold">Tag</td>
					<td style="font-weight: bold">Uses</td>
					<td>&nbsp;&nbsp;&nbsp;</td>
					<td style="font-weight: bold">Tag</td>
					<td style="font-weight: bold">Uses</td>
				</tr>
<?
$i = 0;
$DB->query("SELECT ID, Name, Uses FROM tags WHERE TagType='genre' ORDER BY Name ASC");
$TagCount = $DB->record_count();
$Tags = $DB->to_array();
for ($i = 0; $i < $TagCount / 3; $i++) {
	list($TagID1, $TagName1, $TagUses1) = $Tags[$i];
	list($TagID2, $TagName2, $TagUses2) = $Tags[ceil($TagCount/3) + $i];
	list($TagID3, $TagName3, $TagUses3) = $Tags[2*ceil($TagCount/3) + $i];
?>
				<tr class="<?=(($i % 2)?'rowa':'rowb')?>">
					<td><a href="torrents.php?taglist=<?=$TagName1?>" ><?=$TagName1?></a></td>
					<td><?=$TagUses1?></td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					
					<td><a href="torrents.php?taglist=<?=$TagName2?>" ><?=$TagName2?></a></td>
					<td><?=$TagUses2?></td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
					
					<td><a href="torrents.php?taglist=<?=$TagName3?>" ><?=$TagName3?></a></td>
					<td><?=$TagUses3?></td>
				</tr>
<?
}
?>		
						
			</table>
		
        </div>
    </div>
    <br />
    <h2>Tag Synonyms</h2>
    
    <div class="tagtable">

<?
    
    $Synomyns = $Cache->get_value('all_synomyns');
    if (!$Synomyns) {
        $DB->query("SELECT ts.ID, Synomyn, TagID, t.Name, Uses
                    FROM tag_synomyns AS ts LEFT JOIN tags AS t ON ts.TagID=t.ID 
                    ORDER BY Name ASC");
        $Synomyns = $DB->to_array(false, MYSQLI_BOTH);
        $Cache->cache_value('all_synomyns', $Synomyns);
    } 
    $LastParentTagName ='';
    $Row = 'a';
    
    foreach($Synomyns as $Synomyn) {
        list($SnID, $SnName, $ParentTagID, $ParentTagName, $Uses) = $Synomyn;
        
        if ($LastParentTagName != $ParentTagName) {
            if ($LastParentTagName != '') {  ?>
                
            </table></div>
<?          
            }   ?>
            <div style="display:inline-block">
            <table  class="syntable shadow">
                <tr>
                    <td class="colhead" style="width:200px"><a href="torrents.php?taglist=<?=$ParentTagName?>" ><?=$ParentTagName?></a>&nbsp;(<?=$Uses?>)</td>
                </tr>
<?             
            $LastParentTagName = $ParentTagName;
       }
                $Row = $Row == 'b'?'a':'b';
?>
                <tr class="row<?=$Row?>">
                    <td ><?=$SnName?></td>
                </tr>
<?  }   
    if($SnID){ // only finish if something was in list ?> 
            </table></div>
<?  }    ?> 
	</form>
    </div>
</div>
<?
show_footer();
?>