<?

if (isset($_REQUEST['topic'])) {
    $CurrentTopicID = db_string($_REQUEST['topic']);
} else {
    error(0);
}


$DB->query("SELECT Category, Title, Body, Time, MinClass, SubCat FROM articles WHERE TopicID='$CurrentTopicID'");
if (!list($Category, $Title, $Body, $Time, $MinClass, $SubCat) = $DB->next_record()) {
    error(404);
}
$Body = $Text->full_format($Body, true); // true so regardless of author permissions articles can use adv tags 
$Body = replace_special_tags($Body);

if($MinClass>0){ // check permissions
        // should there be a way for FLS to see these... perm setting maybe?
    if ( $StaffClass < $MinClass ) error(403);
}

$Articles = $Cache->get_value("articles_$Category");
if($Articles===false){
        $DB->query("SELECT TopicID, Title, Description, SubCat, MinClass, IF(SubCat='$SubCat',0,1) AS Sort
                  FROM articles 
                 WHERE Category='$Category'
              ORDER BY Sort, SubCat, Title");
        $Articles = $DB->to_array();
        $Cache->cache_value("articles_$Category", $Articles);
}

$PageTitle = empty($LoggedUser['ShortTitles'])?"{$ArticleCats[$Category]} > $Title":$Title ;
$SubTitle = $ArticleCats[$Category] ." Articles";

show_header( $PageTitle, 'browse,overlib,bbcode');
?>

<div class="thin">
    <h2><?=$SubTitle?></h2>
    
    <div class="head">Search Articles</div> 
    <form method="get" action="articles.php">
        <table>
            <tr class="box">
                <td class="label">Search for:</td>
                <td>
                        <input name="searchtext" type="text" class="long" value="<?=htmlentities($Searchtext)?>" />
                </td>
                <td width="10%">
                        <input type="submit" value="Search" />
                </td>
            </tr>
        </table>
    </form>
    <br/>
    
<?
    $Row = 'a';
    $LastSubCat=-1;
    $OpenTable=false;
    $i=0;
    foreach($Articles as $Article) {
        list($TopicID, $ATitle, $Description, $SubCat, $MinClass) = $Article;
        
        //if($CurrentTopicID==$TopicID) continue;
        if($MinClass>$StaffClass) continue;
        
        $Row = ($Row == 'a') ? 'b' : 'a';

        if($LastSubCat != $SubCat) {
            $Row = 'b';
            $LastSubCat = $SubCat;
            if($OpenTable){  ?>
        </table><br/>
<?           }  ?>
        
 <?       
        if($i==1) {    
?>
    
    <div class="head"><?=$Title?></div>
    <div class="box pad" style="padding:10px 10px 10px 20px;">
        <?=$Body?>
    </div>

<?
        }
        $i++;
?>
        <div class="head"><?=($SubCat==1?"Other $ArticleCats[$Category] articles":$ArticleSubCats[$SubCat])?></div>
        <table width="100%" class="topic_list">
            <tr class="colhead">
                    <td style="width:300px;">Title</td>
                    <td>Additional Info</td>
            </tr>
<? 
            $OpenTable=true;
        }
?>
            <tr class="row<?=$Row?>">

                    <td class="topic_link">
                            <a href="articles.php?topic=<?=$TopicID?>"><?=display_str($ATitle)?></a>
                    </td>
                    <td>
                            <?=display_str($Description)?>
<?                  if($MinClass) { ?>
                        <span style="float:right">
                            <?="[{$ClassLevels[$MinClass][Name]}+]"?>
                        </span>
<?                  } ?>
                    </td>
            </tr>
<?  } ?>
    </table>
</div>


<?
show_footer();
?>