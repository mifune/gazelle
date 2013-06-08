<?
define('RESULTS_PER_PAGE', 100);

// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = "desc") {
    global $OrderBy, $OrderWay;
    if ($SortKey == $OrderBy) {
        if ($OrderWay == "desc") {
            $NewWay = "asc";
        } else {
            $NewWay = "desc";
        }
    } else {
        $NewWay = $DefaultWay;
    }
    return "tags.php?order_way=$NewWay&amp;order_by=$SortKey&amp;" . get_url(array('action', 'order_way', 'order_by'));
}


if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}

if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('Tag', 'Uses', 'Votes', 'TagType'))) {  // ,'Synonyms'
    $_GET['order_by'] = 'Uses';
    $OrderBy = 'Uses'; 
} else {
    $OrderBy = $_GET['order_by'];
}

if( empty($_GET['search_type']) || !in_array($_GET['search_type'], array('tags', 'syns', 'both')) ) 
        $_GET['search_type']='tags';


/*
        list($Page,$Limit) = page_limit(RESULTS_PER_PAGE);

        $DB->query("SELECT SQL_CALC_FOUND_ROWS
                           t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                            SUM(tt.PositiveVotes-1) AS PosVotes,
                            SUM(tt.NegativeVotes-1) AS NegVotes,
                            SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                      FROM tags AS t
                      JOIN torrents_tags AS tt ON tt.TagID=t.ID
                    $WHERE 
                  GROUP BY t.ID
                  ORDER BY $OrderBy $OrderWay
                     LIMIT $Limit");
        
        $Tags = $DB->to_array('TagID', MYSQLI_ASSOC) ;
        $TagIDs = $DB->collect('TagID');
        $TagIDs = implode(',', $TagIDs);
        
        $DB->query("SELECT FOUND_ROWS()");
        list($NumAllTags) = $DB->next_record();

        $title = "$NumAllTags $title";
            
        //$Pages=get_pages($Page,$NumAllTags,RESULTS_PER_PAGE,9);
        
        
        if($NumAllTags>0 ) {
            // get the syns for the tag results
            $DB->query("SELECT Count(ID) as Synonyms , GROUP_CONCAT( Synomyn  SEPARATOR ', ' ) as SynText, TagID 
                          FROM tag_synomyns
                         WHERE TagID IN ( $TagIDs )
                      GROUP BY TagID ");
            $Syns = $DB->to_array('TagID', MYSQLI_ASSOC) ;
            foreach($Tags as $tID=>$TagInfo) {
                if(isset($Syns[$tID])) $Tags[$tID] = array_merge($Syns[$tID], $TagInfo);
            }
        } 
        
        error(print_r( ($Tags) ,true));

$Searchtext = trim($_REQUEST['searchtags']);
$Searchtext_esc = db_string($Searchtext);

if($Searchtext) { 
    $WHERE = array();
    $title = "search results";
    $_GET['search_type']='tags';
    if($_GET['search_type']=='both' || $_GET['search_type']=='tags') $WHERE[] = "t.Name LIKE '%$Searchtext_esc%'";
    if($_GET['search_type']=='both' || $_GET['search_type']=='syns') $WHERE[] = "x.SynText LIKE '%$Searchtext_esc%'";
    if(count($WHERE)>0) $WHERE = "WHERE ".  implode(' OR ', $WHERE);
} else {
    $title = "tags";
    $WHERE  = ""; 
}  */

show_header('Tags');
?>
<div class="thin">
    <h2>Tag Listings</h2>

	<div class="linkbox">
		<a style="font-weight: bold" href="tags.php">[Tags Listings & Search]</a>
		<a href="tags.php?action=synonyms">[Synonyms Lists]</a>
	</div>
    
    <div class="">
<?
        list($Page,$Limit) = page_limit(RESULTS_PER_PAGE);
    
        $Searchtext = trim($_REQUEST['searchtags']);
        $Searchtext_esc = db_string($Searchtext);

        if($Searchtext) { 
            $title = "search results";
            $WHERE1=''; $WHERE2='';
            if($_GET['search_type']=='both' || $_GET['search_type']=='tags') 
                $WHERE1 = "t.Name LIKE '%$Searchtext_esc%'";
            
            if($_GET['search_type']=='both' || $_GET['search_type']=='syns') 
                $WHERE2 = "s.Synomyn LIKE '%$Searchtext_esc%'";
            
            //if(count($WHERE)>0) $WHERE = "WHERE ".  implode(' OR ', $WHERE);
            //else $WHERE  = "";
        } else {
            $title = "tags";
            $WHERE1=''; $WHERE2=''; 
        }

        /*
        $DB->query("SELECT SQL_CALC_FOUND_ROWS
                           t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                           x.Synonyms as Synonyms, x.SynText,
                            SUM(tt.PositiveVotes-1) AS PosVotes,
                            SUM(tt.NegativeVotes-1) AS NegVotes,
                            SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes
                      FROM tags AS t
                      JOIN torrents_tags AS tt ON tt.TagID=t.ID
                 LEFT JOIN ( SELECT TagID, Count(ID) as Synonyms , GROUP_CONCAT( Synomyn  SEPARATOR ', ' ) as SynText 
                               FROM tag_synomyns GROUP BY TagID ) AS x ON x.TagID=t.ID 
                    $WHERE 
                  GROUP BY t.ID
                  ORDER BY $OrderBy $OrderWay
                     LIMIT $Limit"); */ 
        
        if(!$WHERE1 && !$WHERE2 && $Page==1) { // lets cache front page results for a few hours
                
            $CacheResults = $Cache->get_value("tagslist_$OrderBy_$OrderWay");
            
            if($CacheResults===false) { 
                
                $DB->query("SELECT SQL_CALC_FOUND_ROWS
                                       t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                                        SUM(tt.PositiveVotes-1) AS PosVotes,
                                        SUM(tt.NegativeVotes-1) AS NegVotes,
                                        SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                                  FROM tags AS t
                                  JOIN torrents_tags AS tt ON tt.TagID=t.ID 
                              GROUP BY t.ID
                              ORDER BY $OrderBy $OrderWay
                                 LIMIT $Limit");
                
                $Tags = $DB->to_array('TagID', MYSQLI_ASSOC) ;
                $TagIDs = $DB->collect('TagID');
                $TagIDs = implode(', ', $TagIDs);

                $DB->query("SELECT FOUND_ROWS()");
                list($NumAllTags) = $DB->next_record();

                if($NumAllTags>0 ) {
                    // get the syns for the tag results
                    $DB->query("SELECT Count(ID) as Synonyms , GROUP_CONCAT( Synomyn  SEPARATOR ', ' ) as SynText, TagID 
                                  FROM tag_synomyns
                                 WHERE TagID IN ( $TagIDs )
                              GROUP BY TagID ");
                    $Syns = $DB->to_array('TagID', MYSQLI_ASSOC) ;
                    foreach($Tags as $tID=>$TagInfo) {
                        if(isset($Syns[$tID])) $Tags[$tID] = array_merge($Syns[$tID], $TagInfo);
                    }
                }

                $Cache->cache_value("tagslist_$OrderBy_$OrderWay", array($NumAllTags, $Tags), 3600*12);
                
            } else {
                
                list($NumAllTags, $Tags) = $CacheResults;
            }
            
            
        } else {
          
            $NumAllTags=false;

            if($WHERE2  == "" || $_GET['search_type']=='tags') {
                if($WHERE1) $WHERE1 = "WHERE $WHERE1";


                $DB->query("SELECT SQL_CALC_FOUND_ROWS
                                       t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                                        SUM(tt.PositiveVotes-1) AS PosVotes,
                                        SUM(tt.NegativeVotes-1) AS NegVotes,
                                        SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                                  FROM tags AS t
                                  JOIN torrents_tags AS tt ON tt.TagID=t.ID
                                $WHERE1 
                              GROUP BY t.ID
                              ORDER BY $OrderBy $OrderWay
                                 LIMIT $Limit");


            } elseif ($_GET['search_type']=='syns') {

                $DB->query("SELECT SQL_CALC_FOUND_ROWS
                                   t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                                    SUM(tt.PositiveVotes-1) AS PosVotes,
                                    SUM(tt.NegativeVotes-1) AS NegVotes,
                                    SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                              FROM tags AS t
                              JOIN torrents_tags AS tt ON tt.TagID=t.ID
                              JOIN tag_synomyns AS s ON s.TagID=t.ID
                             WHERE $WHERE2 
                          GROUP BY t.ID
                          ORDER BY $OrderBy $OrderWay
                             LIMIT $Limit");
            } else {

                $DB->query("SELECT Count(DISTINCT t.ID)
                              FROM tags AS t
                         LEFT JOIN tag_synomyns AS s ON s.TagID=t.ID
                             WHERE $WHERE1 OR $WHERE2 ");
                list($NumAllTags) = $DB->next_record();


                if($WHERE1) $WHERE1 = "WHERE $WHERE1";
                $DB->query("(SELECT t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                                    SUM(tt.PositiveVotes-1) AS PosVotes,
                                    SUM(tt.NegativeVotes-1) AS NegVotes,
                                    SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                              FROM tags AS t
                              JOIN torrents_tags AS tt ON tt.TagID=t.ID
                            $WHERE1 
                          GROUP BY t.ID
                          ORDER BY $OrderBy $OrderWay)
                        UNION
                            (SELECT t.Name as Tag, Uses, IF(TagType='genre','*','') as TagType, 
                                    SUM(tt.PositiveVotes-1) AS PosVotes,
                                    SUM(tt.NegativeVotes-1) AS NegVotes,
                                    SUM(tt.PositiveVotes-1)-SUM(tt.NegativeVotes-1) As Votes, t.ID as TagID
                              FROM tags AS t
                              JOIN torrents_tags AS tt ON tt.TagID=t.ID
                              JOIN tag_synomyns AS s ON s.TagID=t.ID
                             WHERE $WHERE2 
                          GROUP BY t.ID
                          ORDER BY $OrderBy $OrderWay)
                          ORDER BY $OrderBy $OrderWay
                             LIMIT $Limit");


            }

            $Tags = $DB->to_array('TagID', MYSQLI_ASSOC) ;
            $TagIDs = $DB->collect('TagID');
            $TagIDs = implode(', ', $TagIDs);

            if($NumAllTags===false) {
                $DB->query("SELECT FOUND_ROWS()");
                list($NumAllTags) = $DB->next_record();
            }


            if($NumAllTags>0 ) {
                // get the syns for the tag results
                $DB->query("SELECT Count(ID) as Synonyms , GROUP_CONCAT( Synomyn  SEPARATOR ', ' ) as SynText, TagID 
                              FROM tag_synomyns
                             WHERE TagID IN ( $TagIDs )
                          GROUP BY TagID ");
                $Syns = $DB->to_array('TagID', MYSQLI_ASSOC) ;
                foreach($Tags as $tID=>$TagInfo) {
                    if(isset($Syns[$tID])) $Tags[$tID] = array_merge($Syns[$tID], $TagInfo);
                }
            }
        }
        
        
        $title = "$NumAllTags $title";
            
        $Pages=get_pages($Page,$NumAllTags,RESULTS_PER_PAGE,9);
     
?>
        <div class="head">Tag Search</div>
        <table class="box pad ">
            <form method="get" action="tags.php">
                <tr class="">
                    <td class="label">Search for:</td>
                    <td width="60%">
                        <input name="searchtags" type="text" class="long" value="<?=htmlentities($Searchtext)?>" />
                    </td>
                    
                    <td class="nobr">
                        <input name="search_type" value="tags" type="radio" <?if($_GET['search_type']=='tags')echo 'checked="checked"'?> />Tags &nbsp;&nbsp;
                        <input name="search_type" value="syns" type="radio" <?if($_GET['search_type']=='syns')echo 'checked="checked"'?> />Synonyms &nbsp;&nbsp;
                        <input name="search_type" value="both" type="radio" <?if(!isset($_GET['search_type']) || $_GET['search_type']=='both')echo 'checked="checked"'?> />Both &nbsp;&nbsp;
                        
                    </td> 
                    <td>
                        <input type="submit" value="Search" />
                    </td>
                </tr>
                <tr class="rowa">
                    <td></td>
                    <td colspan="3"><?=$title?></td>
                </tr>
            </form>
        </table>
    
        <div class="linkbox"><?=$Pages?></div>
        
        <div>
            <div class="tag_results">
            <table class="box shadow">
                <tr class="colhead">
                    <td><a href="<?=header_link('Tag') ?>">Tag</a> <a class="tagtype" href="<?=header_link('TagType') ?>">(*official)</a></td>
                    <td class="center"><a href="<?=header_link('Uses') ?>">Uses</a></td> 
                    <td class="center" colspan="2"><a href="<?=header_link('Votes') ?>">Votes</a></td> 
                    <td class="center">Synonyms</td> 
                </tr>
<?  
            $NumTags = count($Tags);
            $i=0;
            //for ($i = 0; $i < $NumTags ; $i++) {
            foreach($Tags as $TagItem) {
                 
                //list($Tag, $Uses, $TagType, $NumSyns, $Synonyms, $PosVotes, $NegVotes) = $Tags[$i];  
                $Tag = $TagItem['Tag']; 
                $NumSyns =  $TagItem['Synonyms'];
                $Synonyms =  $TagItem['SynText'];
                                 
                if ($Searchtext && ($_GET['search_type']=='tags' || $_GET['search_type']=='both'))
                    $TagShow = highlight_text_css($Searchtext, $Tag);    
                else 
                    $TagShow = $Tag;
                
                if ($Searchtext && ($_GET['search_type']=='syns' || $_GET['search_type']=='both'))
                   $SynonymsShow = highlight_text_css($Searchtext, $Synonyms);     
                else
                   $SynonymsShow = $Synonyms;
                 
                $row = $row == 'b'?'a':'b';
?> 
                <tr class="row<?=$row?>"> 
                    <td><?="<a href=\"torrents.php?taglist=$Tag\">$TagShow$TagItem[TagType]</a>"?></td> 
                    <td class="center"><?=$TagItem['Uses']?></td> 
                    <td class="votes center"><?= "+$TagItem[PosVotes]"?></td> 
                    <td class="votes left"><?= "-$TagItem[NegVotes]"?></td> 
                    <td class="center"><?=($NumSyns?$NumSyns:'')?>
<?               if ($NumSyns>0) {   
                    if($Synonyms==$SynonymsShow){
                        $hiddencss = 'hidden ';
                        $hideicon = '[+]';
                    } else {
                        $hiddencss = '';
                        $hideicon = '[-]';
                    }    ?>
                      <span class="plusmn"><a onclick="$('#syns_<?=$i?>').toggle(); this.innerHTML=(this.innerHTML=='[-]'?'[+]':'[-]'); return false;"><?=$hideicon?></a></span>
<?               } else $hiddencss = 'hidden ';       ?>
                    </td> 
                </tr> 
                <tr class="<?=$hiddencss?> row<?=$row?>" id="syns_<?=$i?>">
                    <td colspan="5" class="left"><div class="synonyms row<?=$row?>"><?=$SynonymsShow;?></div></td> 
                </tr> 
<? 
                if ($NumTags>(RESULTS_PER_PAGE/2) && $i== floor(($NumTags-1)/2)) {   ?>
            </table>
            </div>
            <div class="tag_results">
            <table class="box shadow">
                <tr class="colhead">
                    <td><a href="<?=header_link('Tag') ?>">Tag</a> <a class="tagtype" href="<?=header_link('TagType') ?>">(*official)</a></td>
                    <td class="center"><a href="<?=header_link('Uses') ?>">Uses</a></td> 
                    <td class="center"  colspan="2"><a href="<?=header_link('Votes') ?>">Votes</a></td> 
                    <td class="center">Synonyms</td> 
                </tr>
<?
                    }   
                $i++;
                }
?>
            </table>
            </div>
        </div>
        
        <div class="linkbox"><?=$Pages?></div>
    </div>
</div>

<?
show_footer();
?>
