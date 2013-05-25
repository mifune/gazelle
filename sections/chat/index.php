<?
enforce_login();
show_header('IRC');	

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;
/*
$DB->query("SELECT IRCKey FROM users_main WHERE ID = $LoggedUser[ID]");
list($IRCKey) = $DB->next_record();

if(empty($IRCKey)) {
?>
<div class="thin">
	<h3 id="irc">IRC Rules - Please read these carefully!</h3>
	<div class="box pad" style="padding:10px 10px 10px 20px;">
		<p>
			<strong>Please set your IRC Key on your <a href="user.php?action=edit&amp;userid=<?=$LoggedUser['ID']?>">profile</a> first! For more information on IRC, please read the <a href="wiki.php?action=article&amp;name=IRC+-+How+to+join">wiki article</a>.</strong>
		</p>
	</div>
</div>
<?
} else {
    */
	if(!$_POST["connect"] || (!isset($_POST["emp"]) && !isset($_POST["help"]) && !isset($_POST["staff"]))) {
?>
<div class="thin">
	<div class="head">IRC Rules - Please read these carefully!</div>
	<div class="box pad" style="padding:10px 10px 10px 20px;">
            <?
        $Body=get_article('chatrules'); 
        if (!$Body) $Body = "could not find article 'chatrules'"; 
        echo $Text->full_format($Body, true);
         
        ?>
		<!-- 
        <ul>
			<li>
				Staff have the final decision, if they say stop and you continue, expect at least to be banned from the IRC server. 
			</li>
			<li>
				Be respectful to IRC Operators and Administrators. These people are site staff who volunteer their time for little compensation. They are there for the benefit of all and to aid in conflict resolution, do not waste their time.
			</li>
			<li>
				Do not link shock sites or anything NSFW (not safe for work) without a warning. If in doubt, ask a staff member in <?=BOT_HELP_CHAN?> about it.
			</li>
			<li>
				Excessive swearing will get you kicked, keep swearing to a minimum.
			</li>
			<li>
				Do not leave your Caps Lock on all the time. It gets annoying, and you will likely get yourself kicked.
			</li>
			<li>
				No arguing. You can't win an argument over the internet, so you're just wasting your time trying.
			</li>
			<li>
				No prejudice, especially related to race, religion, politics, ethnic background, etc. It is highly suggested to avoid this entirely.
			</li>
			<li>
				Flooding is irritating and will merit you a kick. This includes but is not limited to: automatic now playing scripts, pasting large amounts of text, and multiple consecutive lines with no relevance to the conversation at hand.
			</li>
			<li>
				Impersonation of other members (particularly staff members) will not go unpunished. If you are uncertain of a users identity, check their vhost.
			</li>
			<li>
				Spamming is strictly forbidden. This includes but is not limited to: personal sites, online auctions, and torrent uploads.
			</li>
			<li>
				Obsessive annoyance both to other users and staff will not be tolerated.
			</li>
			<li>
				Don't PM, DCC, or Query anyone you don't know or have never talked to without asking, this applies specifically to staff.
			</li>
			<li>
				No language other than English is permitted in the official IRC channels. If we can't understand it, we can't moderate it. 
			</li>
			<li>
				The offering, selling, trading and giving away of invites to this or any other site on our IRC network is <strong>strictly forbidden</strong>.
			</li>
			<li>
				<strong>Read the topic before asking questions.</strong>
			</li>
		</ul> -->
	</div>
    <form method="post" action="chat.php" onsubmit="return ($('#channel1').raw().checked || $('#channel2').raw().checked || $('#channel3').raw().checked);">
        <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
        <br/>
        <table>
            <tr>
                <td class="noborder right" width="60%">
                     connect to the <strong>#empornium</strong> general chat channel
                    <input type="checkbox" id="channel1" name="emp" value="1" checked="checked" /><br/>
                     connect to the <strong>#empornium-help</strong> channel*
                    <input type="checkbox" id="channel2" name="help" value="1" />
<?                      if ( $LoggedUser['SupportFor'] !="" || $LoggedUser['DisplayStaff'] == 1 ) { ?>
                    <br/> connect to the <strong>#empornium-staff</strong> channel*
                    <input type="checkbox" id="channel3" name="staff" value="1" />
 <?                     }       ?>
                </td>
                <td class="noborder">
                    <input type="submit" id="connect" name="connect" style="width:160px" value="I agree to the rules" />
                </td>
            </tr>
            <tr>
                <td class="noborder right" colspan="2">
                    *note: Please be patient we are not around 24/7. If you want help idle in the help channel (or if you want to help) &nbsp;&nbsp;
                </td>
            </tr>
        </table> 
    </form>
</div>

<?
	} else {
		$nick = $LoggedUser["Username"];
		$nick = preg_replace('/[^a-zA-Z0-9\[\]\\`\^\{\}\|_]/', '', $nick);
		if(strlen($nick) == 0) {
			$nick = "EmpGuest????";
		} else {
			if(is_numeric(substr($nick, 0, 1))) {
				$nick = "_" . $nick;
			}
		}
            $channels='';
            $div='';
            if(isset($_POST["emp"])) {
                $channels='empornium';
                $div='%2c';
            }
            if(isset($_POST["help"])) {
                $channels .= "{$div}empornium-help";
                $div='%2c';
            }
            if(isset($_POST["staff"])) 
                if ( $LoggedUser['SupportFor'] !="" || $LoggedUser['DisplayStaff'] == 1 ) 
                    $channels .= "{$div}empornium-staff"; 
            
            //$channels=$_POST["channel"]=='help'?'empornium-help':'empornium';
            
                    // webchat.digitalwizardry.org 
                    // irc.emprn.tk
                    
                    //http://webchat.digitalirc.org/?prompt=1&uio=MTY9ZmFsc2U67&nick=<?=$nick?/>&channels=<?=$channels?/>
                    //http://shire.digitalirc.org:9090/
                    
                    
?>
<div class="thin">
	<div class="head">IRC</div>
	<div class="box pad center"> 
                <iframe src="http://webchat.digitalirc.org/?prompt=1&uio=MTY9ZmFsc2U67&nick=<?=$nick?>&channels=<?=$channels?>" width="98%" height="600"></iframe>  
	</div>
</div>
<?
	}
 
show_footer();
?>
