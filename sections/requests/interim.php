<?php
if(!isset($_GET['id']) || !is_number($_GET['id'])) { error(404); }

$Action = $_GET['action'];
if($Action != "unfill" && $Action != "delete") {
	error(404);
}

$DB->query("SELECT UserID, FillerID FROM requests WHERE ID = ".$_GET['id']);
list($RequestorID, $FillerID) = $DB->next_record();

if($Action == 'unfill') {
	if($LoggedUser['ID'] != $RequestorID && $LoggedUser['ID'] != $FillerID && !check_perms('site_moderate_requests')) {
		error(403);
	}
} elseif($Action == "delete") {
	if(!check_perms('site_moderate_requests')) {
		error(403);
	}
}

show_header(ucwords($Action)." Request");
?>
<div class="thin center">
	<div style="width:700px; margin:20px auto;">
		<div class="head">
			<?=ucwords($Action)?> Request
		</div>
		<div class="box pad">
			<form action="requests.php" method="post">
				<input type="hidden" name="action" value="take<?=$Action?>" />
				<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
				<input type="hidden" name="id" value="<?=$_GET['id']?>" />
<?php  if($Action == 'delete') { ?>
				<div class="warning">You will <strong>not</strong> get your bounty back if you delete this request.</div>
<?php  } elseif($Action == 'unfill') { ?>
				<div class="warning">Unfilling a request without a valid, nontrivial reason will result in a warning.<br/>If in doubt please message the staff and ask for advice first.</div>
<?php  } ?>
				<strong>Reason:</strong>
				<textarea name="reason" class="long"/></textarea>
				<input value="<?=ucwords($Action)?>" type="submit" />
			</form>
		</div>
	</div>
</div>
<?php
show_footer();
