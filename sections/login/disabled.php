<?
show_header('Disabled');
if(empty($_POST['submit']) || empty($_POST['username'])) {
    ?>
    <p class="warning">
    Your account has been disabled.<br />
    This is either due to inactivity or rule violation.<br />
    To discuss this come to our IRC at: <?=BOT_SERVER?><br />
    And join <?=BOT_DISABLED_CHAN?><br /><br />
    Be honest - at this point, lying will get you nowhere.<br /><br />
    </p>
    <p class="strong">
    If you do not have access to an IRC client you can use the WebIRC interface provided below.<br />
    Please use your empornium username.
    </p>
    <br />
    <form action="" method="post">
          <input type="text" name="username" width="20" />
          <input type="submit" name="submit" value="Join WebIRC" />
    </form>
    <?
} else {
	$nick = $_POST['username'];
	$nick = preg_replace('/[^a-zA-Z0-9\[\]\\`\^\{\}\|_]/', '', $nick);
	if(strlen($nick) == 0) {
		$nick = "EmpGuest?";
	} //else {
		//if(is_numeric(substr($nick, 0, 1))) {
		//	$nick = "_" . $nick;
		//}
	//}
      $nick = "disabled_$nick";
      
    ?>
    <div class="thin">
          <h3 id="general">Disabled IRC</h3>
        <div class="">
              <div class="head">IRC</div>
              <div class="box pad center"> 
                        <iframe src="http://webchat.digitalwizardry.org/?nick=<?=$nick?>&channels=empornium-help" width="98%" height="600"></iframe> 
              </div>
        </div>
    </div>
    <?
}
show_footer();
?>
