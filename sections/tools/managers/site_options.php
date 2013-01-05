<?
if (!check_perms('admin_manage_site_options')) {
    error(403);
}

show_header('Manage Site Options', 'jquery');

$DB->query('SELECT FreeLeech FROM site_options');
list($freeleech) = $DB->next_record();
//$freeleech = "2013-02-12 00:00:00";
?>

<div class="thin">
    <h2>Manage Site Options</h2>

    <table>
        <tr class="head">
            <td>Site Options</td>
        </tr>
        <tr>
            <td>
                <form  id="quickpostform" action="tools.php" method="post">
                    <input type="hidden" name="action" value="take_site_options" />
                    <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
                    <div class="box">	
                        <table id="infodiv" class="shadow">
                            <tr>
                                <td class="label">Freeleech Until (Y-M-D H:M:S):</td>
                                <td>
                                    <? if ($freeleech > sqltime()) { ?>
                                    <?=$freeleech?> (<?=time_diff($freeleech)?> left.)
                                    <? } else { ?>
                                        <input type="text" name="freeleech" size="15" value="0000-00-00 00:00:00" />
                                    <? } ?>
                                </td>                             
                            </tr>
                            <? if ($freeleech > sqltime()) { ?>
                            <td class="label">Remove Freeleech</td>
                            <td>
                                <input type="checkbox" name="remove_freeleech" />
                            </td>
                            <? } ?>
                        </table>
                    </div>
                    <input type="submit" value="Save Changes" />
                </form>
            </td>
        </tr>
    </table>
</div>

<? show_footer(); ?>

