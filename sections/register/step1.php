<?
show_header('Register','validate');
echo $Val->GenerateJS('regform');
?>
    <div>
        <p class="stronger">IMPORTANT: <?=SITE_URL?> is a private tracker, you must maintain a good ratio or your downloading rights will be restricted.
            <br/>Please read the rules carefully!</p>
    </div><br/>
<?

if(empty($Sent)) {
	if(!empty($_REQUEST['invite'])) {
		echo '<input type="hidden" name="invite" value="'.display_str($_REQUEST['invite']).'" />'."\n";
	}
	if(!empty($Err)) {
?>
	<br/><strong class="warning"><?=$Err?></strong><br /><br />
<?	} ?>
    <br/><br/><br/>
<form name="regform" id="regform" method="post" action="" onsubmit="return formVal();">
<div style="width:500px;">
	<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
	<table cellpadding="2" cellspacing="1" border="0" align="center">
		<tr valign="top">
			<td align="right" width="150px">Username&nbsp;</td>
			<td align="left">
				<input type="text" name="username" id="username" class="inputtext" value="<?=(!empty($_REQUEST['username']) ? display_str($_REQUEST['username']) : '')?>" />
				<p style="padding: 10px">It is recommended that you do NOT use your real name for personal security!<!-- We will not be changing it for you.--></p>
			</td>
		</tr>
		<tr valign="top">
			<td align="right">Email&nbsp;</td>
			<td align="left"><input type="text" name="email" id="email" class="inputtext" value="<?=(!empty($_REQUEST['email']) ? display_str($_REQUEST['email']) : (!empty($InviteEmail) ? display_str($InviteEmail) : ''))?>" /></td>
		</tr>
		<tr valign="top">
			<td align="right">Password&nbsp;</td>
			<td align="left"><input type="password" name="password" id="password" class="inputtext" /></td>
		</tr>
		<tr valign="top">
			<td align="right">Verify Password&nbsp;</td>
			<td align="left"><input type="password" name="confirm_password" id="confirm_password" class="inputtext" /></td>
		</tr>
		<!--<tr>
            <td colspan="2">
                <p style="padding: 10px">
                    <strong>Disclaimer:</strong> <br/>
                    None of the files shown here are actually hosted on this server. The links are provided solely by this site's users. 
                    These BitTorrent files are meant for the distribution of backup files. By downloading the BitTorrent file, you are claiming 
                    that you own the original backup file. The administrator of this site http://torrents.empornium.me holds NO RESPONSIBILITY 
                    if these files are misused in any way. For controversial reasons, if you are affiliated with or conducting an investigation 
                    for any government, ANTI-Piracy group or any other related group, or were formally a worker of one you CANNOT download any 
                    of these BitTorrent files. If you download these files but are not agreeing to these terms and you are violating code 
                    431.322.12 of the Internet Privacy Act signed by Bill Clinton in 1995 and that means that you CANNOT threaten our ISP(s) 
                    or any person(s) or company storing these files, and cannot prosecute any person(s) affiliated with this site which includes 
                    family, friends or individuals who run or enter this web site. If you do not agree to these terms do not use our site or 
                    this service otherwise you will face serious legal consequences .
                </p>
            </td>
		</tr>
		<tr valign="top">
			<td></td>
			<td align="left"><input type="checkbox" name="discreq" id="discreq" value="0"<? if (!empty($_REQUEST['discreq'])) { ?> checked="checked"<? } ?> /> <label for="discreq">I have read the above disclaimer and agree to its terms.</label></td>
		</tr>-->
		<tr valign="top">
			<td></td>
			<td align="left"><input type="checkbox" name="readrules" id="readrules" value="1"<? if (!empty($_REQUEST['readrules'])) { ?> checked="checked"<? } ?> /> <label for="readrules">I will read the rules.</label></td>
		</tr>
            <!--
		<tr valign="top">
			<td></td>
			<td align="left"><input type="checkbox" name="readwiki" id="readwiki" value="1"<? if (!empty($_REQUEST['readwiki'])) { ?> checked="checked"<? } ?> /> <label for="readwiki">I will read the wiki.</label></td>
		</tr> -->
		<tr valign="top">
			<td></td>
			<td align="left"><input type="checkbox" name="agereq" id="agereq" value="1"<? if (!empty($_REQUEST['agereq'])) { ?> checked="checked"<? } ?> /> <label for="agereq">I am 18 years of age or older.</label></td>
		</tr>
		<tr>
			<td colspan="2" height="10"></td>
		</tr>
		<tr>
			<td colspan="2" align="right"><input type="submit" name="submit" value="Submit" class="submit" /></td>
		</tr>
	</table>
<? } else { ?>
	An email has been sent to the address that you provided. After you confirm your email address you will be able to log into your account.

<? 		if($NewInstall) { echo "Since this is a new installation, you can log in directly without having to confirm your account."; }
} ?>
</div>
</form>
<?
show_footer();
