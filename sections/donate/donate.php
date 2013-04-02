<?
enforce_login();

$eur_rate = get_current_btc_rate();
 
show_header('Donate','bitcoin');
?>
<!-- Donate -->
<div class="thin">
    <h2>Donate</h2>
    
    <div class="head">Thank-you for considering to make us a donation</div>
    <div class="box pad">
        <? 
        $Body = get_article('donateinline');
        if ($Body) {
            include(SERVER_ROOT.'/classes/class_text.php');
            $Text = new TEXT;

            echo $Text->full_format($Body , get_permissions_advtags($LoggedUser['ID']));  
            //. " [size=2][b][i]".BITCOIN_ADDRESS."[/i][/b][/size]"
        }
        ?>
        <br/>
        <p style="font-size: 1.1em" title="rate is Mt.Gox weighted average: <?=$eur_rate?>">The current bitcoin exchange rate is 1 bitcoin = &euro;<?=number_format($eur_rate,2);?></p>
        <br/>
        <a style="font-weight: bold;font-size: 1.2em;" href="donate.php?action=my_donations&new=1"><span style="color:red;"> >> </span>click here to get a personal donation address<span style="color:red;"> << </span></a>
    </div>
 
    <div class="head">Donate for <img src="<?= STATIC_SERVER ?>common/symbols/donor.png" alt="love" /></div>
    <div class="box pad">
        <p><span style="font-size:1.1em;font-weight: bold;">What you will receive for a suggested minimum &euro;5 donation (<?=number_format(5.0/$eur_rate,3)?> bitcoins) :</span> </p>
        <ul>
            <? if ($LoggedUser['Donor']) { ?>
                <li>Even more love! (You will not get multiple hearts.)</li>
                <li>A warmer fuzzier feeling than before!</li>
            <? } else { ?>
                <li>Our eternal love, as represented by the <img src="<?= STATIC_SERVER ?>common/symbols/donor.png" alt="Donor" /> you get next to your name.</li>
                <? /*  // we are not using invites
                if (USER_LIMIT != 0 && $UserCount >= USER_LIMIT && !check_perms('site_can_invite_always') && !isset($DonorPerms['site_can_invite_always'])) {
                    ?>
                    <li class="warning">Note: Because the user limit has been reached, you will be unable to use the invites received until a later date.</li>
                <? } */ ?>
                <li>A warm fuzzy feeling.</li>
            <? }   
            
            $DB->query("SELECT Title, Description, Image, Cost FROM badges WHERE Type='Donor' ORDER BY Cost");
            if($DB->record_count()>0) {
                ?>
                <li>In order to recognise large contributers we have the following donor medals</li>
                <?
                while( list($title, $desc, $image, $cost) = $DB->next_record()) {
                    ?>
                    <br/><img style="vertical-align: middle;" src="<?= STATIC_SERVER ?>common/badges/<?=$image?>" alt="<?=$title?>" title="<?=$title?>" />  &nbsp; If you donate <span style="font-size: 1.3em;font-weight: bolder">&euro;<?=$cost?></span> you will get a <?=$title?>  <strong>(<?=number_format($cost/$eur_rate,3)?> bitcoins)</strong>
                    <br/>
                    <?
                }
                ?>
                <br/>
                <?
            }
            ?>
            <li><span  style="font-size: 1.2em;">If you want to donate for <img src="<?= STATIC_SERVER ?>common/symbols/donor.png" alt="love" title="love" /> 
                    <a style="font-weight: bold;" href="donate.php?action=my_donations&new=1"><span style="color:red;"> >> </span>click here to get a personal donation address<span style="color:red;"> << </span></a></span></li> 
        </ul>
    </div>

    <div class="head">Donate for <strong>GB</strong></div>
    <div class="box pad">
        <p><span style="font-size:1.1em;font-weight: bold;">What you will receive for your donation:</span></p>
        <ul> 
            <!--
            <li>You will get <?=DEDUCT_GB_PER_EURO?> GB removed from your <u>download</u> total per &euro; donated  <strong>(<?=DEDUCT_GB_PER_EURO?>gb per <?=number_format(1.0/$eur_rate,3)?> bitcoins, <?=number_format($eur_rate*DEDUCT_GB_PER_EURO,2)?>gb per bitcoin)</strong></li>  
            <li>For larger donations a more favourable rate may be available, please enquire.</li>  
            -->
            <?    
            /// $DonateLevels = array ( 1 => 1.0, 10 => 1.5, 50 => 2.0, 100 => 10 );
            
            foreach ($DonateLevels as $level=>$rate) {
                ?>
                    <li>If you donate &euro;<?=$level?> you will get <?=number_format($level * $rate)?> GB removed from your <u>download</u>   <strong>(rate: <?=$rate?>gb per &euro;) &nbsp; ( <?=number_format($level/$eur_rate,3)?> bitcoins)</strong></li>  
            
                <?
            }
            
            ?><br/>
            <li><span style="font-size: 1.2em;">If you want to donate for GB  
                    <a style="font-weight: bold;" href="donate.php?action=my_donations&new=1"><span style="color:red;"> >> </span>click here to get a personal donation address<span style="color:red;"> << </span></a></span></li> 
        </ul>
         
    </div>

    <div class="head">What you will <strong>not</strong> receive</div>
    <div class="box pad">
        <ul>
            <? /* if ($LoggedUser['Donor']) { ?>    // no invites 
                <li>2 more invitations, these were one time only.</li>
            <? } */ ?>
            <li>Immunity from the rules.</li>
            <li>Additional <u>upload</u> credit.</li>
        </ul>
        <p>Please be aware that by making a donation you are not purchasing donor status or invites. You are helping us pay the bills and cover the costs of running the site. We are doing our best to give our love back to donors but sometimes it might take more than 48 hours. Feel free to contact us by sending us a <a href="staffpm.php?action=user_inbox">Staff Message</a> regarding any matter. We will answer as quickly as possible.</p>
    </div>
</div>
<!-- END Donate -->
<? show_footer(); ?>
