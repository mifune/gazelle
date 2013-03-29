<?

authorize();
if (!check_perms('admin_donor_addresses'))  error(403);

// split on whitespace and commas and nl
$input_addresses = trim($_REQUEST['input_addresses']);
$input_addresses = str_replace(array("\n", "  ", " ", ","), "|", $input_addresses);
$input_addresses = explode("|", $input_addresses);
$sql_values = array();
$invalid_addresses = array();
// error(print_r($input_addresses,true));

foreach ($input_addresses as $Key => &$address) {
    // just do a cursory check on format
    $address = trim($address);
    $address = trim($address, "'\"");
    if (!$address) {
        unset($input_addresses[$Key]);
    } else if (validate_btc_address($address)) {  
        $sql_values[] = "('". db_string($address)."','$LoggedUser[ID]')";
    } else {  // not in a valid format
        $invalid_addresses[] = $address;
        //unset($input_addresses[$Key]);
    }
}

// error ( "<br/> num valid: " . count($input_addresses). "<br/>". print_r($input_addresses, true). "<br/><br/>".
 //        "num invalid: " . count($invalid_addresses). "<br/>". print_r($invalid_addresses, true));

if (count($invalid_addresses)==0) {

    $DB->query("INSERT INTO bitcoin_addresses (public, userID) VALUES " . implode(',', $sql_values));
    
    header("Location: tools.php?action=btc_address_input");
} else {
    
    show_header("Invalid addresses");
?>
<div class="thin">
    <h2>Invalid addresses</h2> 
    
    <div class="head"></div>
    <div class="box pad">
        Addresses in red have not passed validation!<br/>
        The regex used to validate addresses is: <?=BTC_ADDRESS_REGEX?>   &nbsp; (This can be changed in the config file)<br/>
        <br/>
        <div class="donate_details">
<?
 
        foreach($input_addresses as $baddress) {
            if (in_array($baddress, $invalid_addresses)) {
                echo "<span style=\"color:red\">$baddress</span><br/>";
            } else {
                echo "$baddress<br/>";
            }
        }
?>        
        </div>
        <? /*
        <form id="addressform" action="donate.php" method="post">
            <input type="hidden" name="action" value="enter_addresses" />
            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
            <input type="hidden" name="input_addresses" value="<?=implode("\n", array_diff($input_addresses, $invalid_addresses) )?>" />
            <input type="submit" value="Enter just valid addresses" /> 
        </form> */ ?>
    </div>
    
</div>
<?
    show_footer();
}
?>
