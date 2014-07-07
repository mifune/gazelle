<?php
function display_perm($Key,$Title,$ToolTip='')
{
    global $Values;
      if (!$ToolTip)$ToolTip=$Title;
    $Perm='<input type="checkbox" name="perm_'.$Key.'" id="'.$Key.'" value="1"';
    if (!empty($Values[$Key])) { $Perm.=" checked"; }
    $Perm.=' /> <label for="'.$Key.'" title="'.$ToolTip.'">'.$Title.'</label><br />';
    echo $Perm;
}

show_header('Manage Permissions','validate');

echo $Val->GenerateJS('permform');

if(isset($_REQUEST['isclass']) &&  $_REQUEST['isclass']=='1') $IsUserClass = true;
?>
<form name="permform" id="permform" method="post" action="" onsubmit="return formVal();">
    <input type="hidden" name="action" value="permissions" />
    <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
    <input type="hidden" name="id" value="<?=display_str($_REQUEST['id'])?>" />
      <input type="hidden" name="isclass" value="<?=($IsUserClass?'1':'0')?>" />
    <div class="linkbox">
        [<a href="tools.php?action=permissions">Back to permission list</a>]
        [<a href="tools.php">Back to Tools</a>]
    </div>
    <table class="permission_head">
<?php if ($IsUserClass) {     ?>
        <tr>
            <td class="label">User Class<!--Permission Name--></td>
            <td><input type="text" name="name" id="name" value="<?=(!empty($Name) ? display_str($Name) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Class Level</td>
            <td><input type="text" name="level" id="level" value="<?=(!empty($Level) ? display_str($Level) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Max Sig length</td>
            <td><input type="text" name="maxsiglength" value="<?=(!empty($MaxSigLength) ? display_str($MaxSigLength) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Max Avatar Size</td>
            <td><input class="wid35" type="text" name="maxavatarwidth" value="<?=(!empty($MaxAvatarWidth) ? display_str($MaxAvatarWidth) : '')?>" />
                      &nbsp;x&nbsp;
                      <input type="text"  class="wid35" name="maxavatarheight" value="<?=(!empty($MaxAvatarHeight) ? display_str($MaxAvatarHeight) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Rank Color</td>
            <td><input type="text" name="color" style="font-weight:bold;color: #<?=display_str($Color)?>" value="<?=(!empty($Color) ? display_str($Color) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Show on Staff page</td>
            <td><input type="checkbox" name="displaystaff" value="1" <?php  if (!empty($DisplayStaff)) { ?>checked<?php  } ?> /></td>
        </tr>
        <tr>
            <td class="label">Maximum number of personal collages</td>
            <td><input type="text" name="maxcollages" size="5" value="<?=$Values['MaxCollages']?>" /></td>
        </tr>
<?php   } else {    ?>
        <tr>
            <td class="label">Group Permission</td>
            <td><input type="text" name="name" id="name" value="<?=(!empty($Name) ? display_str($Name) : '')?>" /></td>
        </tr>
        <tr>
            <td class="label">Rank Color</td>
            <td><input type="text" name="color" style="font-weight:bold;color: #<?=display_str($Color)?>" value="<?=(!empty($Color) ? display_str($Color) : '')?>" /></td>
        </tr>
<?php       }

if (is_numeric($_REQUEST['id'])) { ?>
        <tr>
            <td class="label">Current users in this class</td>
            <td><?=number_format($UserCount)?></td>
        </tr>
<?php  } ?>
    </table>
<?php
include(SERVER_ROOT."/classes/permissions_form.php");
permissions_form();
?>
</form>
<?php
show_footer();
