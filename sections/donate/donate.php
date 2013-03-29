<?
//TODO: Developer, add resend last donation when available AND add missing headers to Test IPN
enforce_login();
 

   $eur_rate = get_current_btc_rate();

// $DonorPerms = get_permissions(DONOR);

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
        /*
        ?>
        <br/>
        <input type="button" onclick="CheckAddress('','')" value="check result" />
        <span id="bcresult"></span>
        <br/>
         *  .' &nbsp; &nbsp;  <em><a href="http://anonym.to/?https://mtgox.com/index.html?Currency=EUR">rate is from Mt.Gox weighted average '.$eur_rate.'</a></em>'
        <?  */
        ?>
        <br/>
        <p style="font-size: 1.1em" title="rate is Mt.Gox weighted average: <?=$eur_rate?>">The current bitcoin exchange rate is 1 bitcoin = &euro;<?=number_format($eur_rate,2);?></p>
        
    </div>

    <div class="head">Donate for <strong>GB</strong></div>
    <div class="box pad">
        <p><span style="font-size:1.1em;font-weight: bold;">What you will receive for your donation:</span></p>
        <ul> 
            <li>You will get 1 GB removed from your <u>download</u> total per &euro; donated  <strong>(1gb per <?=number_format(1.0/$eur_rate,3)?> bitcoins)</strong></li>  
            <li>For larger donations a more favourable rate may be available, please enquire.</li>  
            <li><span  style="font-size: 1.1em;">If you want to donate for GB  
                     <a style="font-weight: bold;" href="donate.php?action=my_donations&new=1">click here to get a personal donation address</a></span></li> 
        </ul>
         
    </div>

    <!-- or 2 BTC minimum donation // please... bitcoin?? -->
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
            <? } ?>
                  
            
            <li>In order to recognise large contributers we have the following donor medals</li>
            <img style="vertical-align: middle;" src="<?= STATIC_SERVER ?>common/badges/doner-1.png" alt="Bronze Donor" title="Donor Bronze Heart" />  &nbsp; If you donate <span style="font-size: 1.3em;font-weight: bolder">&euro;10</span> you will get a bronze donors medal  <strong>(<?=number_format(10.0/$eur_rate,3)?> bitcoins)</strong>
            <br/>
            <br/><img style="vertical-align: middle;" src="<?= STATIC_SERVER ?>common/badges/doner-2.png" alt="Silver Donor" title="Donor Silver Heart" />  &nbsp; If you donate <span style="font-size: 1.3em;font-weight: bolder">&euro;25</span> you will get a silver donors medal  <strong>(<?=number_format(25.0/$eur_rate,3)?> bitcoins)</strong>
            <br/>
            <br/><img style="vertical-align: middle;" src="<?= STATIC_SERVER ?>common/badges/doner-3.png" alt="Gold Donor" title="Donor Gold Heart" />  &nbsp; If you donate <span style="font-size: 1.3em;font-weight: bolder">&euro;50</span> you will get a gold donors medal  <strong>(<?=number_format(50.0/$eur_rate,3)?> bitcoins)</strong>
            <br/>
            <br/><img style="vertical-align: middle;" src="<?= STATIC_SERVER ?>common/badges/doner-4.png" alt="Diamond Donor" title="Donor Diamond Heart" />  &nbsp; If you donate <span style="font-size: 1.3em;font-weight: bolder">&euro;100</span> you will get a diamond donors medal  <strong>(<?=number_format(100.0/$eur_rate,3)?> bitcoins)</strong>
            <br/>
            <li><span  style="font-size: 1.1em;">If you want to donate for <img src="<?= STATIC_SERVER ?>common/symbols/donor.png" alt="love" title="love" /> 
                    <a style="font-weight: bold;" href="donate.php?action=my_donations&new=1">click here to get a personal donation address</a></span></li> 
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
