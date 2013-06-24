<?

authorize();

//Set by system
if(!$_POST['groupid'] || !is_number($_POST['groupid'])) {
	error(404);
}
$GroupID = $_POST['groupid'];

//Usual perm checks
if(!check_perms('torrents_edit')) {
	$DB->query("SELECT UserID FROM torrents WHERE GroupID = ".$GroupID);
	if(!in_array($LoggedUser['ID'], $DB->collect('UserID'))) { 
		error(403);
	}
}


if(check_perms('torrents_freeleech') && isset($_POST['freeleech'])) {   
       /* xor isset($_POST['neutralleech']) xor isset($_POST['unfreeleech']))) {
	if(isset($_POST['freeleech'])) {
		$Free = 1;
	} elseif(isset($_POST['neutralleech'])) {
		$Free = 2;
	} else {
		$Free = 0;
	} */
      $Free = (int)$_POST['freeleech'];
      $Free = $Free==1?1:0;
      
      /*
	if(isset($_POST['freeleechtype']) && in_array($_POST['freeleechtype'], array(0,1,2,3))) {
		$FreeType = $_POST['freeleechtype'];
	} else {
		error(404);
	} */

	freeleech_groups($GroupID, $Free);    //, $FreeType);
}

header("Location: torrents.php?id=".$GroupID);

?>
