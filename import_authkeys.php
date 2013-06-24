<?
set_time_limit(50000);
error_reporting(E_ALL); // was 0 (off)


function is_number($Str) {
    $Return = true;
    if ($Str < 0) {
        $Return = false;
    }
    // We're converting input to a int, then string and comparing to original
    $Return = ($Str == strval(intval($Str)) ? true : false);
    return $Return;
}

function make_secret2($Length = 32) {
    $Secret = '';
    $Chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 0; $i < $Length; $i++) {
        $Rand = mt_rand(0, strlen($Chars) - 1);
        $Secret .= substr($Chars, $Rand, 1);
    }
    return str_shuffle($Secret);
}

 

require('/var/www/emp3/classes/config.php');


$time_start = microtime(true);

echo "connecting to database<br/>";
 

$link = mysqli_connect(SQLHOST, SQLLOGIN, SQLPASS, SQLDB, SQLPORT, SQLSOCK); // defined in config.php
			
if (!$link) {
	 echo "error connection to db ".mysqli_connect_errno().' '. mysqli_connect_error().'<br/>';
}
            
 
    
    echo "Update users: generate new authkeys, CatchupTime=UTC_TIMESTAMP()<br/>";
    $sqltime = date('Y-m-d H:i:s', time());

    if (!mysqli_query($link, "UPDATE gazelle.users_info
        SET AuthKey =
            MD5(
                CONCAT(
                    AuthKey, RAND(), '".mysqli_real_escape_string($link, make_secret2())."',
                    SHA1(
                        CONCAT(
                            RAND(), RAND(), '".mysqli_real_escape_string($link, make_secret2())."'
                        )
                    )
                )
            ), CatchupTime='$sqltime';")) {
            die(mysqli_error($link));
     }
     
    
    echo "creating new invite_tree table for users<br/>";

    $result = mysqli_query($link, 'select ID from gazelle.users_main');
    if (!$result) die(mysqli_error($link));

    $TreeIndex = 2;
    $values = array();
    $comma = "";
    while (($row = mysqli_fetch_assoc($result))) {
        $values[] = "(".$row["ID"].", 0, $TreeIndex, 0, 2)";
        $TreeIndex++;
    }

    if (!mysqli_query($link, "TRUNCATE TABLE gazelle.invite_tree;")) die(mysqil_error($link));

    if (!mysqli_query($link, "insert into gazelle.invite_tree values ".implode(',',$values)))
            die(mysqil_error($link));

 


 

$time = microtime(true) - $time_start;
echo "<br/>execution time: $time seconds<br/>";

?>
