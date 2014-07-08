<?php
if (!check_perms('admin_manage_site_options')) {
    error(403);
}

show_header('Manage Site Options', 'jquery');

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
                            <option value="0" <?php  if ($FullLogging=='0') echo 'selected="selected"' ;?>>Off</option>
                            <option value="1" <?php  if ($FullLogging=='1') echo 'selected="selected"' ;?>>Only user.php</option>
                            <option value="2" <?php  if ($FullLogging=='2') echo 'selected="selected"' ;?>>Nearly All (excludes some common known ajax calls)</option>
                            <option value="3" <?php  if ($FullLogging=='3') echo 'selected="selected"' ;?>>Absolutely All</option>
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
                    <td class="label"> <?php  if (!$Sitewide_Freeleech_On) echo "Set ";?>Sitewide Freeleech Until<br/>(Y-M-D H:M:S)</td>
                    <td>
                        <?php  if ($Sitewide_Freeleech_On) {

                            echo date('Y-m-d H:i:s', strtotime($Sitewide_Freeleech) - (int) $LoggedUser['TimeOffset']);
                            echo "  (". time_diff($Sitewide_Freeleech) ." left.)";

                           } else {
                        ?>

                             <input type="text" title="enter the time the sitewide freeleech should expire" name="freeleech" size="18" value="<?=date('Y-m-d', time() - (int) $LoggedUser['TimeOffset'])?> 00:00:00" />
                        <?php   }
                            echo " (time now is: ".date('Y-m-d H:i:s', time() - (int) $LoggedUser['TimeOffset']).")"; // [UTC ".
                        ?>
                    </td>
                </tr>
            <?php  if ($Sitewide_Freeleech_On) { ?>
                <tr>
                    <td class="label">Remove Freeleech</td>
                    <td>
                        <input type="checkbox" name="remove_freeleech" />
                    </td>
                </tr>
            <?php  } ?>
                <tr>
                    <td colspan="2">
                        <input type="submit" value="Save Changes" />
                    </td>
                </tr>
            </table>
        </form>
    </div>

</div>

<?php
show_footer();
