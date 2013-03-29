<?
if(!check_perms('admin_donor_log')) { error(403); }

include(SERVER_ROOT.'/sections/donate/functions.php');
define('DONATIONS_PER_PAGE', 50);


// generate a graph of monthly donations (last 18 months)
if ( !isset($_GET['page']) && !$DonationTimeline = $Cache->get_value('donation_timeline')) {  
	include(SERVER_ROOT.'/classes/class_charts.php');
	
    $DB->query("SELECT DATE_FORMAT(received,'%b \'%y') AS Month, SUM(amount_euro) 
                       FROM bitcoin_donations WHERE state!='unused' 
                       GROUP BY Month ORDER BY received DESC LIMIT 18"); 
    
    /*
	$DB->query("SELECT STR_TO_DATE( DATE_FORMAT(received,'%X%V Monday'), '%X%V %W') AS WeekNum, SUM(amount_euro)  
                       FROM bitcoin_donations WHERE state!='unused' 
                       GROUP BY WeekNum ORDER BY received DESC LIMIT 18");
    */
    
	$Timeline = $DB->to_array(false,MYSQLI_NUM);
	//$Timeline[] = array('', '0');
	$Timeline = array_reverse($Timeline);
    //$Timeline = array( array('one',5) , array('two',15) , array('three',25) , array('four',35) , array('six',45) , array('eight',48) , array('seven',50) );
	$Area = new AREA_GRAPH(880,160); // ,array('Break'=>1)
	foreach($Timeline as $Entry) {
		list($Label,$Amount) = $Entry;
		$Area->add($Label,$Amount);
	}
	$Area->transparent();
	$Area->grid_lines();
	$Area->color('3d7930');
	$Area->lines(2);
	$Area->generate();
	$DonationTimeline = $Area->url();
	$Cache->cache_value('donation_timeline',$DonationTimeline,mktime(0,0,0,date('n')+1,2));
}



$view = $_GET['view'];
if (!$view || !in_array($view, array('issued','submitted','cleared'))) $view='submitted';

if ($view == 'issued') {
    $statesql= 'unused';
    //$statesql= "!='unused'";
    $unused = true;
} else {
    $statesql= $view;
    //$statesql= "='unused'";
    $unused = false;
}

show_header('Donation log','bitcoin');
?>
<div class="thin">
    <h2>Donation log</h2> 
<?
    if (!isset($_GET['page'])) {
?>
    <br />
    <div class="head">Donation history</div>
    <div class="box pad">
        <img src="<?=$DonationTimeline?>" /><br/>
<?
        $DB->query("SELECT Count(ID), SUM(amount_euro) FROM bitcoin_donations WHERE state!='unused'");
        list($totalnum, $totalsum) = $DB->next_record();
        
?>
        <div class="right">
            <span class="size3"><?=$totalnum?> donations</span><br/>
             (value when submitted) <span class="size3">&euro; <?=number_format($totalsum,2)?> total</span><br/>
            <? //=print_r($Timeline,true); ?>
        </div>
    </div>
    
<?  } 

    list($Page,$Limit) = page_limit(DONATIONS_PER_PAGE);
 
    $DB->query("SELECT SQL_CALC_FOUND_ROWS
                        bc.ID, state, public, bc.time, bc.userID, bitcoin_rate, received, amount_bitcoin, amount_euro, comment,
                        m.Username, m.PermissionID, m.Enabled, i.Donor, i.Warned  
                     FROM bitcoin_donations AS bc
                     LEFT JOIN users_main AS m ON m.ID=bc.UserID
                     LEFT JOIN users_info AS i ON i.UserID=bc.UserID 
                     WHERE state ='$statesql' 
                     ORDER BY received DESC, bc.time DESC LIMIT $Limit ");

    $Donations = $DB->to_array(false,MYSQLI_NUM);
    $DB->query("SELECT FOUND_ROWS()");
    list($Results) = $DB->next_record();
    
    $eur_rate = get_current_btc_rate();

?>
    <!--<div>
        <form action="" method="get">
            <table cellpadding="6" cellspacing="1" border="0" class="border" width="100%">
                <tr>
                    <td class="label"><strong>Email:</strong></td>
                    <td>
                        <input type="hidden" name="action" value="donation_log" />
                        <input type="text" name="search" size="60" value="<? if (!empty($_GET['search'])) { echo display_str($_GET['search']); } ?>" />
                        &nbsp;
                        <input type="submit" value="Search log" />
                    </td>
                </tr>
            </table>	
        </form>
    </div>
    <br />-->
    <div class="linkbox">
    <?
        $Pages=get_pages($Page,$Results,DONATIONS_PER_PAGE,11) ;
        echo $Pages;
    ?>
    </div>

    <h2><?=  ucfirst($view); // ($unused?'Issued Addresses':'Submitted Donations')?> Donations</h2>
      
	<div class="linkbox"> 
        <? if (check_perms('admin_donor_addresses')) { ?>
		<a href="tools.php?action=btc_address_input">[Unused address pool]</a>
        <? } ?>
		<a href="tools.php?action=donation_log&view=issued">[Issued addresses]</a>
		<a href="tools.php?action=donation_log&view=submitted">[Submitted donations]</a>
		<a href="tools.php?action=donation_log&view=cleared">[Cleared donations]</a>
	</div>
    <br/>
    
    <?
        if ($unused) {
            $timeheader = "time issued";
        } else { 
            $timeheader = "time received";
        }
            
        $numthispage = count($Donations);
    ?>

    <div class="head"></div>
    <div class="box pad">
        <? 
        if ($eur_rate=='0'){   ?>
            <span class="red">The site was unable to get an exchange rate</span> - hopefully this is a temporary issue with the MtGox webservice, 
                if it persists we will have to find another way to get/set the exchange rate!
    <?  } else { ?>
            <span style="font-size: 1.1em" title="rate is Mt.Gox weighted average: <?=$eur_rate?>">
                                The current bitcoin exchange rate is 1 bitcoin = &euro;<?=number_format($eur_rate,2);?></span>
    <?  } ?>
       
        <br/><br/>
        <div class="donate_details<?=($unused?'':' green')?>">
            <table class="noborder">
                <tr>
                    <td></td>
                    <td>
                        <!--<input type="button" onclick="ChangeStatesToCleared('<?=$numthispage?>')" value="change all state to cleared" />-->
                    </td>  
                    <td colspan="<?=($unused?'4':'6')?>" style="text-align:right;">
    <?                  if ($numthispage>0){      ?>
                            <span title="query all btc balances on this page (dont hammer the webservice too much though)">
                            <a style="cursor: pointer;" onclick="CheckAddressLoadNext('1','<?=$eur_rate?>','6','<?=$numthispage?>','<?=($unused?'0':'1')?>');"><img src="<?= STATIC_SERVER ?>common/symbols/reload1.gif" alt="query" /></a> &nbsp;
                            <a style="cursor: pointer;" onclick="CheckAddressLoadNext('1','<?=$eur_rate?>','6','<?=$numthispage?>','<?=($unused?'0':'1')?>');">query all btc balances</a>
                            </span>
    <?                  }                   ?>
                    </td>
                </tr>
                <tr class="colhead">
                    <td>user</td>
                    <td>address</td>
                    <td><?=$timeheader?></td>
                    <td>btc <?=($unused?'balance':'(submitted)')?></td>
                    <td>&euro; <?=($unused?' (estimated)':' (submitted)')?></td>
    <?                  if (!$unused){      ?>
                            <td>btc (now)</td>
                            <td>&euro; (now)</td>
    <?                  }                   ?>
                </tr>
    <? 
            $i=0;
            foreach($Donations as $Donation) {
                list($ID, $state, $public, $activetime, $UserID, $bitcoin_rate, $received, $amount_bitcoin, $amount_euro, $comment,
                        $Username, $PermissionID, $Enabled, $Donor, $Warned) = $Donation;
                $i++;
                if ($state == 'unused') {
                    $time = time_diff($activetime);
                } else {
                    $time = time_diff($received);
                }
                $row = $row=='b'?'a':'b';
    ?>
                <tr class="row<?=$row?>">
                    <td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled, $PermissionID)?> 
                            <a style="font-style: italic;font-size:0.8em;" href="donate.php?action=my_donations&userid=<?=$UserID?>" target="_blank" title="view users my donations page">[view log]</a></td>
                    <td><span class="address" id="address_<?=$i?>"><?=$public?></span></td><td><?=$time?></td>
                        
    <?                  if (!$unused){       ?>
                            <td><?=$amount_bitcoin?></td>
                            <td><?=$amount_euro?></td>
    <?                  }                   ?>
                        
                    <td><? //print_btc_query_now($i, $eur_rate, $public);?>
                            <span style="font-style: italic;" id="btc_button_<?=$i?>">
                                <a href="#" onclick="CheckAddress('<?=$i?>','<?=$eur_rate?>','<?=$public?>','6','<?=($unused?'0':'1')?>');return false;">
                                    <img src="<?= STATIC_SERVER ?>common/symbols/reload1.gif" title="query btc balance" alt="query" /></a>
                            </span>&nbsp;
                            <span id="btc_balance_<?=$i?>"></span>
                    </td>
                    <td><span style="font-style: italic;" id="euros_<?=$i?>"></span></td>
                </tr>
                <? if ($state!='unused') {  ?>
                    <tr class="row<?=$row?>">
                        <td><strong>status: <span id="status_<?=$i?>"><?=$state?></span></strong></td>
                        <td colspan="4"><?=$comment?></td>
                        <td colspan="2"><span id="state_button_<?=$i?>"></span></td>
                    </tr>
                <? }                        ?>
    <?      }                       ?>
                </table>
            </div>
    </div>
    <div class="linkbox">
        <?=$Pages?>
    </div>
</div>
<? show_footer(); ?>