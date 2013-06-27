<?
//TODO: rewrite this, make it cleaner, make it work right, add it common stuff
if (!check_perms('admin_create_users')) {
    error(403);
}


if (!empty($_POST['submit'])) {
    
    $Val->SetFields('username', true, 'regex', 'You did not enter a valid username.', array('regex' => '/^[a-z0-9_?]{1,20}$/iD'));
    $Val->SetFields('email', true, 'email', 'You did not enter a valid email address.');
    $Val->SetFields('password', true, 'string', 'You did not enter a valid password (6 - 40 characters).', array('minlength' => 6, 'maxlength' => 40));
    $Val->SetFields('confirm_password', true, 'compare', 'Your passwords do not match.', array('comparefield' => 'password'));

    //$Val->SetFields('Username',true,'regex','You did not enter a valid username.',array('regex'=>'/^[A-Za-z0-9_\-\.]{1,20}$/i'));
    //$Val->SetFields('Password','1','string','You entered an invalid password.',array('maxlength'=>'40','minlength'=>'6'));

    $Err = $Val->ValidateForm($_POST);
    if ($Err) error($Err);

    //Create variables for all the fields
    $Username = trim($_POST['username']);
    $Email = trim($_POST['email']);
    $Password = trim($_POST['password']);

    $DB->query("SELECT ID FROM users_main WHERE Username='" . db_string($Username) . "'");
    if ($DB->record_count() > 0) error("A User with name '$Username' already exists");

    //Create hashes...
    $Secret = make_secret();
    $torrent_pass = make_secret();

    //Create the account
    $DB->query("INSERT INTO users_main (Username,Email,PassHash,Secret,torrent_pass,Enabled,PermissionID,Uploaded) 
            VALUES ('" . db_string($Username) . "','" . db_string($Email) . "','" . db_string(make_hash($Password, $Secret)) . "','" . db_string($Secret) . "','" . db_string($torrent_pass) . "','1','" . APPRENTICE . "', '524288000')");

    //Increment site user count
    $Cache->increment('stats_user_count');

    //Grab the userid
    $UserID = $DB->inserted_id();

    ////update_tracker('add_user', array('id' => $UserID, 'passkey' => $torrent_pass));
    //Default stylesheet
    $DB->query("SELECT ID FROM stylesheets WHERE `Default`='1'");
    list($StyleID) = $DB->next_record();

    //Auth key
    $AuthKey = make_secret();

    //Give them a row in users_info
    $DB->query("INSERT INTO users_info 
		(UserID,StyleID,AuthKey,JoinDate,RunHour) VALUES 
		('" . db_string($UserID) . "','" . db_string($StyleID) . "','" . db_string($AuthKey) . "', '" . sqltime() . "', FLOOR( RAND() * 24 ))");

        
    $Body = get_article("intro_pm");
    if($Body) send_pm($UserID, 0, db_string("Welcome to ". SITE_NAME) , db_string($Body));
    
    update_tracker('add_user', array('id' => $UserID, 'passkey' => $torrent_pass));

    //Redirect to users profile
    header("Location: user.php?id=" . $UserID);
    
    

//Form wasn't sent -- Show form
} else {
 
    show_header('Create a User');

    ?>
    <div class="thin">
        <h2>Create a User</h2>

        <form method="post" action="" name="create_user">
            <input type="hidden" name="action" value="create_user" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <table cellpadding="2" cellspacing="1" border="0" align="center">
                <tr valign="top">
                    <td align="right" class="label">Username&nbsp;</td>
                    <td align="left" class="medium"><input type="text" name="username" id="username" class="inputtext"  maxlength="20" pattern="[A-Za-z0-9_\-\.]{1,20}"  /></td>
                </tr>
                <tr valign="top">
                    <td align="right" class="label">Email&nbsp;</td>
                    <td align="left"><input type="text" name="email" id="email" class="inputtext" /></td>
                </tr>
                <tr valign="top">
                    <td align="right" class="label">Password&nbsp;</td>
                    <td align="left"><input type="password" name="password" id="password" class="inputtext" /></td>
                </tr>
                <tr valign="top">
                    <td align="right" class="label">Verify Password&nbsp;</td>
                    <td align="left"><input type="password" name="confirm_password" id="confirm_password" class="inputtext" /></td>
                </tr>
                <tr>
                    <td colspan="2" align="right"><input type="submit" name="submit" value="Create User" class="submit" /></td>
                </tr>
            </table>
        </form>
    </div>
    <? 
    show_footer();
}

?>
