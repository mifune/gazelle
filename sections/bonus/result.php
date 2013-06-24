<?

    

		show_header('Shop result');
?>
    <h2>shop result</h2>
	<div class="thin">
		<div class="head">result</div>
        <div class="box pad ">
                    <h3 class="center"><?=display_str($_REQUEST['result'])?></h3> 
        </div>
        
		<div class="head">return</div>
        <div class="box pad ">
            
            <a href="bonus.php" title="Bonus Shop">Return to the Bonus Shop</a><br />
            
<?              if (isset($_REQUEST['retu']) && is_number($_REQUEST['retu'])) {   
                    $DB->query("SELECT Username From users_main WHERE ID='".db_string($_REQUEST['retu'])."'"); 
                    if(($DB->record_count()) > 0) {
                        list($Uname) = $DB->next_record();    ?>
                        <a href="user.php?id=<?=$_REQUEST['retu']?>" title="Return to user profile">Return to <?=$Uname?>'s profile</a><br />
<?                  }         
                }  
                if (isset($_REQUEST['rett']) && is_number($_REQUEST['rett'])) {   
                    $DB->query("SELECT Name From torrents_group WHERE ID='".db_string($_REQUEST['rett'])."'"); 
                    if(($DB->record_count()) > 0) {
                        list($Tname) = $DB->next_record();    ?>
                        <a href="torrents.php?id=<?=$_REQUEST['rett']?>" title="Bonus Shop">Return to <?=$Tname?></a><br />
<?                  }         
                }   ?>
        </div>
	</div>
<?
		show_footer();
?>
