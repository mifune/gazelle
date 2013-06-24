<?
show_header('Recover Password','validate');
if(empty($_POST['submit']) || empty($_POST['username'])) {
 
        echo $Validate->GenerateJS('recoverform');
        ?>
        <form name="recoverform" id="recoverform" method="post" action="" onsubmit="return formVal();">
              <div style="width:320px;">
                    <font class="titletext">Reset your password - Step 1</font><br /><br />
        <?
        if(empty($Sent) || (!empty($Sent) && $Sent!=1)) {
              if(!empty($Err)) {
        ?>
                    <font color="red"><strong><?=$Err ?></strong></font><br /><br />
        <?	} ?>
              An email will be sent to your email address with information on how to reset your password<br />
              (check your spam folder if you haven't recieved it within a few minutes)<br /><br />
              <label for="email">Email&nbsp;</label>
              <input type="text" name="email" id="email" class="inputtext" />
              <input type="submit" name="reset" value="Reset!" class="submit" />
 
        <? } else { ?>
              An email has been sent to you, please follow the directions in that email to reset your password.<br />
              (check your spam folder if you haven't recieved it within a few minutes)<br />
        <? } ?>
              </div>
        </form>
        <br/><br/><br/>
        <p class="strong">
            If you need help you can come to our IRC at: <?=BOT_SERVER?><br />
            And join <?=BOT_DISABLED_CHAN?><br /><br />
            If you do not have access to an IRC client you can use the WebIRC interface provided below.<br />
            Please use your empornium username.
        </p>
        <br />
        <form action="" method="post">
              <input type="hidden" name="act" value="recover" />
              <input type="text" name="username" width="20" />
              <input type="submit" name="submit" value="Join WebIRC" />
        </form>
        <?
} else {
    
        $nick = $_POST['username'];
        $nick = preg_replace('/[^a-zA-Z0-9\[\]\\`\^\{\}\|_]/', '', $nick);
        if(strlen($nick) == 0) {
		$nick = "EmpGuest?";
        } 
        $nick = "nologin_$nick";
      
        ?>
    <div class="thin">
        <div class="thin">
              <h3 id="general">IRC Help</h3>
            <div class="">
                  <div class="head">IRC</div>
                  <div class="box pad center"> 
                            <iframe src="http://shire.digitalirc.org:9090/?prompt=1&uio=MTY9ZmFsc2U67&nick=<?=$nick?>&channels=empornium-help" width="98%" height="600"></iframe> 
                  </div>
            </div>
        </div>
    </div>
        <?
}

show_footer();
?>
