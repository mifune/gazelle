<?
if (!check_perms('admin_manage_site_options')) {
    error(403);
}

show_header('Manage Site Options', 'jquery');

//$DB->query('SELECT FreeLeech FROM site_options');
//list($freeleech) = $DB->next_record();
////$freeleech = "2013-02-12 00:00:00";

//$sitewide_freeleech_on = $sitewide_freeleech > sqltime();

?>

<div class="thin">
    <h2>Manage Site Options</h2>

    <div class="head">
        Logging
    </div>
    <div class="box">
        <form action="tools.php" method="post">
            <input type="hidden" name="action" value="change_logging" />
            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
            <table class="shadow">
                <tr>
                    <td class="label">Page Request Logging:</td>
                    <td>
                        <select name="logging">
                            <option value="0" <? if ($FullLogging=='0') echo 'selected="selected"' ;?>>Off</option>
                            <option value="1" <? if ($FullLogging=='1') echo 'selected="selected"' ;?>>Only user.php</option>
                            <option value="2" <? if ($FullLogging=='2') echo 'selected="selected"' ;?>>Nearly All (excludes some common known ajax calls)</option>
                            <option value="3" <? if ($FullLogging=='3') echo 'selected="selected"' ;?>>Absolutely All</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Save Changes" />
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <div class="head">
        Sitewide Freeleech
    </div>
    <div class="box">	
        <form  id="quickpostform" action="tools.php" method="post">
            <input type="hidden" name="action" value="take_site_options" />
            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
            <table id="infodiv" class="shadow">
                <tr>
                    <td class="label"> <? if (!$Sitewide_Freeleech_On) echo "Set ";?>Sitewide Freeleech Until<br/>(Y-M-D H:M:S)</td>
                    <td>
                        <? if ($Sitewide_Freeleech_On) {
                            
                            echo date('Y-m-d H:i:s', strtotime($Sitewide_Freeleech) - (int) $LoggedUser['TimeOffset']);  
                            echo "  (". time_diff($Sitewide_Freeleech) ." left.)"; 
                            
                           } else { 
                            /* ?>  
                                    <select name="freeleech">
                                        <option value="0" selected="<?=!$swfl_on?'seleced':''?>">None</option>
                                        <option value="24">24 hours</option>
                                        <option value="48">48 hours</option>
                                        <option value="168">1 week</option>
                                        <option value="87648">10 years</option>
                                    <? if ($swfl_on) { ?>
                                        <option value="1" selected="selected"><?=time_diff($freeleech, 2, false,false,0)?> (current)</option>
                                    <? } ?>
                                    </select> */ ?>
                            
                             <input type="text" title="enter the time the sitewide freeleech should expire" name="freeleech" size="18" value="<?=date('Y-m-d', time() - (int) $LoggedUser['TimeOffset'])?> 00:00:00" /> 
                        <?  } 
                            echo " (time now is: ".date('Y-m-d H:i:s', time() - (int) $LoggedUser['TimeOffset']).")"; // [UTC ". 
                                   // ((int) $LoggedUser['TimeOffset']<0?'+':'-').((int) $LoggedUser['TimeOffset']/3600)."]" ;
                        ?>
                    </td>                             
                </tr>
            <? if ($Sitewide_Freeleech_On) { ?>
                <tr>
                    <td class="label">Remove Freeleech</td>
                    <td>
                        <input type="checkbox" name="remove_freeleech" />
                    </td>
                </tr>
            <? } ?>
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Save Changes" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
     
</div>

<? show_footer(); ?>

