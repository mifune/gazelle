<?php
show_header('Manage Permissions');
?>
<script type="text/javascript" language="javascript">
//<![CDATA[
function confirmDelete(id) {
	if (confirm("Are you sure you want to remove this permission class?")) {
		location.href="tools.php?action=permissions&removeid="+id;
	}
	return false;
}
//]]>
</script>
<div class="thin">
      <h2>User Classes</h2>
	<div class="linkbox">
		[<a href="tools.php?action=permissions&amp;id=new&amp;isclass=1">Create a new User Class<!--permission set--></a>]
		[<a href="tools.php">Back to Tools</a>]
	</div>
<?php
$DB->query("SELECT p.ID,p.Name,p.Level,p.DisplayStaff,p.MaxSigLength,p.MaxAvatarWidth,
                   p.MaxAvatarHeight, p.Color, COUNT(u.ID)
                   FROM permissions AS p LEFT JOIN users_main AS u ON u.PermissionID=p.ID
                   WHERE p.IsUserClass='1'
                   GROUP BY p.ID
                   ORDER BY p.IsUserClass DESC, p.Level ASC");
if($DB->record_count()) {
?>
	<table>
		<tr class="colhead">
			<td width="16%" class="center">Name</td>
			<td width="10%" class="center">Level</td>
			<td width="14%" class="center">Max Sig Length</td>
			<td width="14%" class="center">Max Avatar Size</td>
			<td width="8%" class="center">Color</td>
			<td width="10%" class="center">Is Staff</td>
			<td width="10%" class="center">User Count</td>
			<td width="18%" class="center">Actions</td>
		</tr>
<?php 	while(list($ID,$Name,$Level,$DisplayStaff,$MaxSigLength,$MaxAvatarWidth,$MaxAvatarHeight,$Color,$UserCount)=$DB->next_record()) {

?>
		<tr>
			<td class="center"><span style="font-weight:bold;color: #<?=display_str($Color)?>"><?=display_str($Name); ?></span></td>
			<td class="center"><?=$Level; ?></td>
			<td class="center"><?=$MaxSigLength; ?></td>
			<td class="center"><?=($MaxAvatarWidth.' x '.$MaxAvatarHeight); ?></td>
            <td class="center"><span style="font-weight:bold;display:block;width:100%;height:100%;color:white;background-color: #<?=display_str($Color)?>">#<?=$Color?></span></td>
			<td class="center"><?=$DisplayStaff=='1'?'<strong>True</strong>':'False'; ?></td>
			<td class="center"><?=number_format($UserCount); ?></td>
			<td class="center">[<a href="tools.php?action=permissions&amp;id=<?=$ID ?>">Edit</a> | <a href="#" onclick="return confirmDelete(<?=$ID?>)">Remove</a>]</td>
		</tr>
<?php 	} ?>
	</table>
<?php  } else { ?>
	<h3 align="center">There are no permission classes.</h3>
<?php  } ?>

      <br/>
      <h2>Group Permissions</h2>
	<div class="linkbox">
		[<a href="tools.php?action=permissions&amp;id=new&amp;isclass=0">Create a new Group Permissions</a>]
		[<a href="tools.php">Back to Tools</a>]
	</div>

<?php
$DB->query("SELECT p.ID,p.Name,p.IsUserClass,p.Color, COUNT(u.ID)
                   FROM permissions AS p LEFT JOIN users_main AS u ON u.GroupPermissionID=p.ID
                   WHERE p.IsUserClass='0'
                   GROUP BY p.ID
                   ORDER BY p.IsUserClass DESC, p.Level ASC");
if($DB->record_count()) {
?>
	<table style="width:50%;margin:0px auto;">
		<tr class="colhead">
			<td width="18%">Name</td>
			<td width="10%">User Count</td>
			<td width="8%" class="center">Color</td>
			<td width="20%" class="center">Actions</td>
		</tr>
<?php 	while(list($ID,$Name,$IsUserClass,$Color,$UserCount)=$DB->next_record()) {  ?>
		<tr>
			<td><?=display_str($Name); ?></td>
			<td><?=number_format($UserCount); ?></td>
			<td class="center"><span style="font-weight:bold;display:block;width:100%;height:100%;color:white;background-color: #<?=display_str($Color)?>">#<?=$Color?></span></td>
			<td class="center">[<a href="tools.php?action=permissions&amp;id=<?=$ID ?>">Edit</a> | <a href="#" onclick="return confirmDelete(<?=$ID?>)">Remove</a>]</td>
		</tr>
<?php 	} ?>
	</table>
<?php  } else { ?>
	<h3 align="center">There are no group permissions.</h3>
<?php  } ?>
</div>
<?php
show_footer();
