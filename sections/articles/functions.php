<?php



function print_articles($Articles, $StaffClass=0, $SkipSubArticle = -1) {
    global $ArticleCats, $ArticleSubCats, $ClassLevels;
    
    $Row = 'a';
    $LastSubCat=-1;
    $OpenTable=false;
     
    foreach($Articles as $Article) {
        list($TopicID, $ATitle, $Description, $SubCat, $MinClass) = $Article;
        
        //if($CurrentTopicID==$TopicID) continue;
        if($MinClass>$StaffClass) continue;
        if($SubCat==$SkipSubArticle) continue;
        
        $Row = ($Row == 'a') ? 'b' : 'a';

        if($LastSubCat != $SubCat) {
            $Row = 'b';
            $LastSubCat = $SubCat;
            if($OpenTable){  ?>
        </table><br/>
<?           }  ?>
        

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
        </table><br/>
<?

}


function replace_special_tags($Body) {
    global $DB, $Cache, $LoggedUser, $Text;
    
    // Deal with special article tags.
    if (preg_match("/\[clientlist\]/i", $Body)) {
        if (!$BlacklistedClients = $Cache->get_value('blacklisted_clients')) {
            $DB->query('SELECT vstring FROM xbt_client_blacklist WHERE vstring NOT LIKE \'//%\' ORDER BY vstring ASC');
            $BlacklistedClients = $DB->to_array(false,MYSQLI_NUM,false);
            $Cache->cache_value('blacklisted_clients',$BlacklistedClients,604800);
        }

        $list = '<table cellpadding="5" cellspacing="1" border="0" class="border" width="100%">
                    <tr class="colhead">
                      <td style="width:150px;"><strong>Banned Clients</strong></td>
                </tr>';                                        

        $Row = 'a';
        foreach($BlacklistedClients as $Client) {
            //list($ClientName,$Notes) = $Client;
            list($ClientName) = $Client;
            $Row = ($Row == 'a') ? 'b' : 'a';
            $list .= "<tr class=row$Row>
                            <td>$ClientName</td>
                      </tr>";
        }
        $list .= "</table>";
        $Body = preg_replace("/\[clientlist\]/i", $list, $Body);
    }

    // imagehost whitelist
    if (preg_match("/\[whitelist\]/i", $Body)) {

        $ImageWhitelist = $Cache->get_value('imagehost_whitelist');
        if($ImageWhitelist === FALSE) {
                $DB->query("SELECT 
                    Imagehost, 
                    Link,
                    Comment,
                    Time,
                    Hidden
                    FROM imagehost_whitelist
                    WHERE Hidden='0'
                    ORDER BY Time DESC");
                $ImageWhitelist = $DB->to_array();
                $Cache->cache_value('imagehost_whitelist', $ImageWhitelist);
        }
        $list = '<table id="whitelist">
                    <tr class="colhead">
                      <td style="width:50%;"><strong>Imagehost</strong></td>
                      <td><strong>Comment</strong></td>
                </tr>';                                        

        $Row = 'a';
        foreach($ImageWhitelist as $ImageHost) { 

            list($Host, $Link, $Comment, $Updated) = $ImageHost; 
            $Row = ($Row == 'a') ? 'b' : 'a';
            $list .= "<tr class=row$Row>
                            <td>".$Text->full_format($Host);
             if ( !empty($Link) && $Text->valid_url($Link)) { 
                     $list .=   "<a href=\"$Link\"  target=\"_blank\"><img src=\"". STATIC_SERVER .'common/symbols/offsite.gif" width="16" height="16" alt="Goto '.$Host."\" /></a>\n";
             }

             $list .=   "</td>
                            <td>".$Text->full_format($Comment)."</td>
                      </tr>";
        }
        $list .= "</table>";

        $Body = preg_replace("/\[whitelist\]/i", $list, $Body);
    }

    // DNU list
    if (preg_match("/\[dnulist\]/i", $Body)) {

        $DNUlist = $Cache->get_value('do_not_upload_list');
        if($DNUlist === FALSE) {
                $DB->query("SELECT  Name, Comment, Time FROM do_not_upload ORDER BY Time");
                $DNUlist = $DB->to_array();
                $Cache->cache_value('do_not_upload_list', $DNUlist);
        }
        $list = '<table id="dnulist">
                    <tr class="colhead">
                      <td style="width:50%;"><strong>Name</strong></td>
                      <td><strong>Comment</strong></td>
                    </tr>';                                        

        $Row = 'a';
        foreach($DNUlist as $BadUpload) { 

            list($Name, $Comment, $Updated) = $BadUpload; 
            $Row = ($Row == 'a') ? 'b' : 'a';
            $list .= "<tr class=row$Row>
                            <td>".$Text->full_format($Name)."</td>
                            <td>".$Text->full_format($Comment)."</td>
                      </tr>";
        }
        $list .= "</table>";

        $Body = preg_replace("/\[dnulist\]/i", $list, $Body);
    }


    if (preg_match("/\[ratiolist\]/i", $Body)) {
        $list = '<table>
                      <tr class="colhead">
                            <td>Amount downloaded</td>
                            <td>Required ratio (0% seeded)</td>
                            <td>Required ratio (100% seeded)</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] < 5*1024*1024*1024?'a':'b').'">
                            <td>0-5GB</td>
                            <td>0.00</td>
                            <td>0.00</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 5*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 10*1024*1024*1024?'a':'b').'">
                            <td>5-10GB</td>
                            <td>0.10</td>
                            <td>0.00</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 10*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 20*1024*1024*1024?'a':'b').'">
                            <td>10-20GB</td>
                            <td>0.15</td>
                            <td>0.00</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 20*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 30*1024*1024*1024?'a':'b').'">
                            <td>20-30GB</td>
                            <td>0.20</td>
                            <td>0.00</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 30*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 40*1024*1024*1024?'a':'b').'">
                            <td>30-40GB</td>
                            <td>0.30</td>
                            <td>0.05</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 40*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 50*1024*1024*1024?'a':'b').'">
                            <td>40-50GB</td>
                            <td>0.40</td>
                            <td>0.10</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 50*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 60*1024*1024*1024?'a':'b').'">
                            <td>50-60GB</td>
                            <td>0.50</td>
                            <td>0.20</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 60*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 80*1024*1024*1024?'a':'b').'">
                            <td>60-80GB</td>
                            <td>0.50</td>
                            <td>0.30</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 80*1024*1024*1024 && $LoggedUser['BytesDownloaded'] < 100*1024*1024*1024?'a':'b').'">
                            <td>80-100GB</td>
                            <td>0.50</td>
                            <td>0.40</td>
                      </tr>
                      <tr class="row'.($LoggedUser['BytesDownloaded'] >= 100*1024*1024*1024?'a':'b').'">
                            <td>100+GB</td>
                            <td>0.50</td>
                            <td>0.50</td>
                      </tr>
                </table>';

        $Body = preg_replace("/\[ratiolist\]/i", $list, $Body);

    }
    return $Body;
}


?>
