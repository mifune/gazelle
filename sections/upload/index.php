<?
enforce_login();
if(!check_perms('site_upload')) { error(403); }
if($LoggedUser['DisableUpload']) {
	error('Your upload privileges have been revoked.');
}

if(!empty($_POST['submit'])) {  
           // $Err ="upload";
    include(SERVER_ROOT.'/sections/upload/upload_handle.php');   
    
} elseif(!empty($_POST['delete'])) {     
    // delete a template
    /* -------  Get template ------- */
    $TemplateID = (int)$_POST['template'];
    $candelete=true;
    $Template = $Cache->get_value('template_' . $TemplateID);
    if ($Template === FALSE) { //it should be cached from upload page 
            $DB->query("SELECT 
                                        t.ID,
                                        t.UserID,
                                        t.Name, 
                                        t.Public
                                   FROM upload_templates as t
                                  WHERE t.ID='$TemplateID'");
            list($Template) = $DB->to_array(false, MYSQLI_BOTH); //dont cache as we have only pulled a subset for this rare edge case (impossible?)
    }
    if (!check_perms('delete_any_template')){
        if($Template['Public'] == 1) {
            $Err = "You cannot delete public templates";
            $candelete=false;
        } elseif($Template['UserID'] != $LoggedUser['ID']) 
            error(0); // naughty
    }
    if($candelete){
            $Err = "Deleted '$Template[Name]' template";
        $DB->query("DELETE FROM upload_templates WHERE ID='$TemplateID'");
        $Cache->delete_value('template_' . $TemplateID);
        $Cache->delete_value('templates_ids_' . $Template['UserID']);
    }
    $HideDNU = true;
    $HideWL = true;
    include(SERVER_ROOT.'/sections/upload/upload.php');  
      
} else {
    
    switch ($_GET['action']){
          case 'add_template': // ajax call
                include(SERVER_ROOT.'/sections/upload/add_template.php');
                break;
          case 'delete_template': // ajax call
                include(SERVER_ROOT.'/sections/upload/delete_template.php');
                break;
            
        default:
           // $Err ="default";
                include(SERVER_ROOT.'/sections/upload/upload.php');
    }
}
?>
