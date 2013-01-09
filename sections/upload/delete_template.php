<?
 
/* -------  Get template ------- */
if (!is_number($_POST['template']) || !check_perms('use_templates') ) error(403);

include(SERVER_ROOT.'/sections/upload/functions.php'); 

// delete a template
    
/* -------  Get template ------- */
$TemplateID = (int)$_POST['template'];
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
    
$candelete=true;
    
if (!check_perms('delete_any_template')){
        if($Template['Public'] == 1) {
            //$Err = "You cannot delete public templates";
            $Result = array(0, "You cannot delete public templates");
            $candelete=false;
            
        } elseif($Template['UserID'] != $LoggedUser['ID']) {  // naughty
            $Result = array(0, "You do not have permission to delete that template");
            $candelete=false;
           // error(0); // naughty
        }
}
    
    
if($candelete){
        //$Err = "Deleted '$Template[Name]' template";
        $DB->query("DELETE FROM upload_templates WHERE ID='$TemplateID'");
        $Cache->delete_value('template_' . $TemplateID);
           
        if ($Template['Public']) $Cache->delete_value('templates_public');
        else $Cache->delete_value('templates_ids_' . $LoggedUser['ID']);
        
        //$Cache->delete_value('templates_ids_' . $Template['UserID']);
        
        $Result = array(1, "Deleted '$Template[Name]' template");
}
 

$Result[] = get_templatelist_html($LoggedUser['ID'], $TemplateID);

echo json_encode($Result);



//echo json_encode(array($Result, get_templatelist_html($GroupID, $_POST['tagsort'])));

/*
$TemplateID = (int)$_POST['template'];
$Results = $Cache->get_value('template_' . $TemplateID);
    
if ($Results === FALSE) {
        $DB->query("SELECT 
                                    t.ID,
                                    t.UserID,
                                    t.Name, 
                                    t.Title, 
                                    t.CategoryID AS Category,
                                    t.Title,
                                    t.Image,
                                    t.Body AS GroupDescription,
                                    t.Taglist AS TagList,
                                    t.TimeAdded,
                                    t.Public,
                                    u.Username AS Authorname
                               FROM upload_templates as t
                          LEFT JOIN users_main AS u ON u.ID=t.UserID
                              WHERE t.ID='$TemplateID'");
        list($Results) = $DB->to_array(false, MYSQLI_BOTH);
        if($Results){
            $Results['GroupDescription'] .= "\n\n\n[br][bg=#0074b7][bg=#0074b7,90%][color=white][align=right][b][i][font=Courier New]$Results[Name] template by $Results[Authorname][/font][/i][/b][/align][/color][/bg][/bg]";
            $Cache->cache_value('template_' .$TemplateID, $Results, 96400 * 7);
            
            // only the uploader can use this to prefill (if not a public template)
            if ($Results['Public']==0 && $Results['UserID'] != $LoggedUser['ID']) {
                unset($Results); 
            }
            
        } else { // catch the case where a public template has been unexpectedly removed but left in a random users cache
            $Cache->delete_value('templates_ids_' .$LoggedUser['ID']); // remove from their template list
            $Results = "That template has been deleted - sorry!";
        }
}
 
    
echo json_encode($Results);
   */

?>
