<?

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
$Text = new TEXT;

$Subject = $_REQUEST['subject'];
if( !empty($_REQUEST['prependtitle']))  $Subject = $_REQUEST['prependtitle'] . $Subject;
if ( !empty($_REQUEST['message'])) $Body = $_REQUEST['message'];
else $Body = $_REQUEST['body'];

echo'
			  <h2>'. display_str($Subject).'</h2>
                    <div class="box">
                        <div class="head">
                               '. format_username($LoggedUser['ID'], $LoggedUser['Username'], $LoggedUser['Donor'], $LoggedUser['Warned'], $LoggedUser['Enabled'] == 2 ? false : true, $LoggedUser['PermissionID'], $LoggedUser['Title'], true). '  Just now
                        </div>
                        <div class="body">'.$Text->full_format($Body, isset($LoggedUser['Permissions']['site_advanced_tags']) &&  $LoggedUser['Permissions']['site_advanced_tags']).'</div>
                    </div>';
   
?>