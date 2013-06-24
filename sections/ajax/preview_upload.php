<?
/* AJAX Previews, simple stuff. */

include(SERVER_ROOT.'/classes/class_text.php'); // Text formatting class
include(SERVER_ROOT . '/classes/class_validate.php');
$Text = new TEXT;
$Validate = new VALIDATE;

//******************************************************************************//
//--------------- Validate data in upload form ---------------------------------//
//** note: if the same field is set to be validated more than once then each time it is set it overwrites the previous test
//** ie.. one test per field max, last one set for a specific field is what is used
//$Validate->SetFields('title', '1', 'string', 'Title must be between 2 and 200 characters.', array('maxlength' => 200, 'minlength' => 2));
//$Validate->SetFields('tags', '1', 'string', 'You must enter at least one tag. Maximum length is 10000 characters.', array('maxlength' => 10000, 'minlength' => 2));
$whitelist_regex = get_whitelist_regex();
$Validate->SetFields('image', '0', 'image', 'The image URL you entered was not valid.', array('regex' => $whitelist_regex, 'maxlength' => 255, 'minlength' => 12));
$Validate->SetFields('desc', '1', 'desc', 'Description', array('regex' => $whitelist_regex, 'minimages'=>1, 'maxlength' => 1000000, 'minlength' => 20));
//$Validate->SetFields('category', '1', 'inarray', 'Please select a category.', array('inarray' => array_keys($NewCategories)));
//$Validate->SetFields('rules', '1', 'require', 'Your torrent must abide by the rules.');

$Err = $Validate->ValidateForm($_POST, $Text); // Validate the form

if ($Err){
    
    echo '<div class="messagebar alert">'.$Err.'</div>';
}  
    
    $Content = $_REQUEST['desc']; // Don't use URL decode.
    $Content .= "[br][br]$_REQUEST[templatefooter]";
    $Imageurl = $_REQUEST['image']; // Don't use URL decode.

    if (!empty($Imageurl)) {
        if ($Text->valid_url($Imageurl)){ 
		$Imageurl = '<img style="max-width: 100%;" src="'.$Imageurl.'" onclick="lightbox.init(this,220);" />';
            //$Imageurl = $Text->full_format('[align=center][img]'.$Imageurl.'[/img][/align]',false,true);
        } else {
            $Imageurl = "<div style=\"text-align: center;\"><strong class=\"important_text\">Not a valid url</strong></div>";
        }
    }  else {
        $Imageurl = "<div style=\"text-align: center;\"><strong class=\"important_text\">No Cover Image</strong></div>";
    }




    echo '<br/>
          <div class="cover_image">
                <div class="head">Cover Image</div>
                <div class="box box_albumart">'.$Imageurl .'</div>
          </div>
          <div class="head">Description</div>
          <div class="box pad">
               '.$Text->full_format($Content, get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions']),true).'                              
          </div><br/>';
    
 /*
    echo '<table cellpadding="3" cellspacing="1" border="0" class="noborder" width="100%">
        <tr>
            <td class="label">Cover Image</td>
            <td> 
                '.$Imageurl .'    
            </td>
        </tr> 
        <tr>
            <td class="label">Description</td>
            <td>
                '.$Text->full_format($Content, get_permissions_advtags($LoggedUser['ID'], $LoggedUser['CustomPermissions']),true).'                              
            </td>
        </tr> 
    </table>';
    
 */

   
?>

