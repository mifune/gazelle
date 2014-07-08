<?php
if (!check_perms('admin_donor_log')) { error(403); }

include(SERVER_ROOT.'/sections/donate/functions.php');
define('DONATIONS_PER_PAGE', 50);

// generate a graph of monthly donations (last 18 months)
if ( !isset($_GET['page']) && !$DonationTimeline = $Cache->get_value('donation_timeline')) {
    include(SERVER_ROOT.'/classes/class_charts.php');

    $DB->query("SELECT DATE_FORMAT(received,'%b \'%y') AS Month, SUM(amount_euro)
                       FROM bitcoin_donations WHERE state!='unused'
                       GROUP BY Month ORDER BY received DESC LIMIT 18");

    $Timeline = $DB->to_array(false,MYSQLI_NUM);
    //$Timeline[] = array('', '0');
    $Timeline = array_reverse($Timeline);
    $Area = new AREA_GRAPH(880,160); // ,array('Break'=>1)
    foreach ($Timeline as $Entry) {
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
    $unused = true;
} else {
    $statesql= $view;
    $unused = false;
}

show_header('Donation log','bitcoin');
?>
<div class="thin">
    <h2>Donation log</h2>
<?php
    if (!isset($_GET['page'])) {
?>
    <br />
    <div class="head">Donation history</div>
    <div class="box pad">
        <img src="<?=$DonationTimeline?>" /><br/>
<?php
        $DB->query("SELECT Count(ID), SUM(amount_euro) FROM bitcoin_donations WHERE state!='unused'");
        list($totalnum, $totalsum) = $DB->next_record();

?>
        <div class="right">
            <span class="size3"><?=$totalnum?> donations</span><br/>
             (value when submitted) <span class="size3">&euro; <?=number_format($totalsum,2)?> total</span><br/>
        </div>
    </div>

<?php   }

    list($Page,$Limit) = page_limit(DONATIONS_PER_PAGE);

    $DB->query("SELECT SQL_CALC_FOUND_ROWS
                        bc.ID, state, public, bc.time, bc.userID, bitcoin_rate, received, amount_bitcoin, amount_euro, comment, bc.staffID, staff.Username,
                        m.Username, m.PermissionID, m.Enabled, i.Donor, i.Warned
                     FROM bitcoin_donations AS bc
                     LEFT JOIN users_main AS m ON m.ID=bc.userID
                     LEFT JOIN users_info AS i ON i.UserID=bc.userID
                     LEFT JOIN users_main AS staff ON staff.ID=bc.staffID
                     WHERE state ='$statesql'
                     ORDER BY received DESC, bc.time DESC LIMIT $Limit ");

    $Donations = $DB->to_array(false,MYSQLI_NUM);
    $DB->query("SELECT FOUND_ROWS()");
    list($Results) = $DB->next_record();

    $eur_rate = get_current_btc_rate();

?>
    <div class="linkbox">
    <?php
        $Pages=get_pages($Page,$Results,DONATIONS_PER_PAGE,11) ;
        echo $Pages;
    ?>
    </div>

    <h2><?=  ucfirst($view); // ($unused?'Issued Addresses':'Submitted Donations')?> Donations</h2>

    <div class="linkbox">
        <?php  if (check_perms('admin_donor_addresses')) { ?>
        <a href="tools.php?action=btc_address_input">[Unused address pool]</a>
        <?php  } ?>
        <a href="tools.php?action=donation_log&view=issued">[Issued addresses]</a>
        <a href="tools.php?action=donation_log&view=submitted">[Submitted donations]</a>
        <a href="tools.php?action=donation_log&view=cleared">[Cleared donations]</a>
    </div>
    <br/>

    <?php
        if ($unused) {
            $timeheader = "time issued";
        } else {
            $timeheader = "time received";
        }

        $numthispage = count($Donations);
    ?>

    <div class="head"></div>
    <div class="box pad">
        <?php
        if ($eur_rate=='0') {   ?>
            <span class="red">The site was unable to get an exchange rate</span> - hopefully this is a temporary issue with the coindesk webservice,
                if it persists we will have to find another way to get/set the exchange rate!
    <?php   } else { ?>
            <span style="font-size: 1.1em" title="rate is Mt.Gox weighted average: <?=$eur_rate?>">
                                The current bitcoin exchange rate is 1 bitcoin = &euro;<?=number_format($eur_rate,2);?></span>
    <?php   } ?>

        <br/><br/>
        <div class="donate_details<?=($unused?'':' green')?>">
            <table class="noborder">
                <tr>
                    <td colspan="2"></td>
                    <td colspan="<?=($unused?'4':'6')?>" style="text-align:right;">
    <?php                   if ($numthispage>0) {      ?>
                            <span title="query all btc balances on this page (dont hammer the webservice too much though)">
                            <a style="cursor: pointer;" onclick="CheckAddressLoadNext('1','<?=$eur_rate?>','6','<?=$numthispage?>','<?=($unused?'0':'1')?>');"><img src="<?= STATIC_SERVER ?>common/symbols/reload1.gif" alt="query" /></a> &nbsp;
                            <a style="cursor: pointer;" onclick="CheckAddressLoadNext('1','<?=$eur_rate?>','6','<?=$numthispage?>','<?=($unused?'0':'1')?>');">query all btc balances</a>
                            </span>
    <?php                   }                   ?>
                    </td>
                </tr>
                <tr class="colhead">
                    <td>user</td>
                    <td>address</td>
    <?php
                    $admin_addresses = check_perms('admin_donor_addresses');
                    if ($admin_addresses) {      ?>
                        <td>issued by</td>
    <?php               }                   ?>
                    <td><?=$timeheader?></td>
                    <td>btc <?=($unused?'balance':'(submitted)')?></td>
                    <td>&euro; <?=($unused?' (estimated)':' (submitted)')?></td>
    <?php               if (!$unused) {      ?>
                        <td>btc (now)</td>
                        <td>&euro; (now)</td>
    <?php               }                   ?>
                </tr>
    <?php
            $i=0;
            foreach ($Donations as $Donation) {
                list($ID, $state, $public, $activetime, $UserID, $bitcoin_rate, $received, $amount_bitcoin, $amount_euro, $comment,
                        $staffID, $staffname, $Username, $PermissionID, $Enabled, $Donor, $Warned) = $Donation;
                $i++;
                if ($state == 'unused') {
                    $time = time_diff($activetime);
                } else {
                    $time = time_diff($received);
                }
                $row = $row=='b'?'a':'b';
    ?>
                <tr class="row<?=$row?> record<?=$i?>">
                    <td><?=format_username($UserID, $Username, $Donor, $Warned, $Enabled, $PermissionID)?>
                            <a style="font-style: italic;font-size:0.8em;" href="donate.php?action=my_donations&userid=<?=$UserID?>" target="_blank" title="view users my donations page">[view log]</a></td>
                    <td><span class="address" id="address_<?=$i?>" <?=$add_title?>><?=$public?></span></td>
    <?php               if ($admin_addresses) {      ?>
                        <td><?=$staffname?></td>
    <?php               }                   ?>
                    <td><?=$time?></td>

    <?php                   if (!$unused) {       ?>
                            <td><?=$amount_bitcoin?></td>
                            <td><?=$amount_euro?></td>
    <?php                   }                   ?>

                    <td>
                        <span style="font-style: italic;" id="btc_button_<?=$i?>">
                            <a href="#" onclick="CheckAddress('<?=$i?>','<?=$eur_rate?>','<?=$public?>','6','<?=($unused?'0':'1')?>');return false;">
                                <img src="<?= STATIC_SERVER ?>common/symbols/reload1.gif" title="query btc balance" alt="query" /></a>
                        </span>&nbsp;
                        <span id="btc_balance_<?=$i?>"></span>
                    </td>
                    <td><span style="font-style: italic;" id="euros_<?=$i?>"></span></td>
                </tr>
                <?php  if ($state!='unused') {  ?>
                    <tr class="row<?=$row?> record<?=$i?>">
                        <td><strong>status: <span id="status_<?=$i?>"><?=$state?></span></strong></td>
                        <td colspan="<?=($admin_addresses?'4':'3')?>"><?=$comment?></td>
                        <td colspan="1"><?php  if ($admin_addresses) { ?><input type="button" onclick="ChangeState('<?=$i?>','<?=$public?>','unused', true)" value="unsubmit(!)" title="unsubmitting a donation sets its state back to just issued - this means the current balance can be resubmitted as a new donation. NOTE: this is for testing/emergency use only, it does not undo donor medals or -gb, it just changes the status of the donation so it can be resubmitted." /><?php  } ?></td>
                        <!--<td colspan="2"><span id="state_button_<?=$i?>"></span></td> -->
                        <td colspan="2" id="state_button_<?=$i?>"><?php  if ($admin_addresses && $state=='cleared') { ?><input type="button" onclick="ChangeState('<?=$i?>','<?=$public?>','submitted', false)" value="unclear(!)" title="unclearing a donation sets its state back to submitted. NOTE: this is for testing/un-fucking records that should not have been cleared, all it does is change the status of the donation back to submitted." /><?php  } ?></td>

                    </tr>
                <?php  }                        ?>
    <?php       }                       ?>
                </table>
            </div>
    </div>
    <div class="linkbox">
        <?=$Pages?>
    </div>
</div>
<?php
show_footer();
