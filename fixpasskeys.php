<?

error_reporting(E_ALL);

require('classes/config.php');

function make_secret2($Length = 32) {
    $Secret = '';
    $Chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 0; $i < $Length; $i++) {
        $Rand = mt_rand(0, strlen($Chars) - 1);
        $Secret .= substr($Chars, $Rand, 1);
    }
    return str_shuffle($Secret);
}

$time_start = microtime(true);

echo "connecting to database\n";

$link = mysqli_connect(SQLHOST, SQLLOGIN, SQLPASS, SQLDB, SQLPORT, SQLSOCK); // defined in config.php

if (!$link) {
    echo "error connection to db " . mysqli_connect_errno() . " " . mysqli_connect_error() . "\n";
}

echo "Reading old passkeys.\n";

// Read in all passkeys
$passkeys = array();
$result = mysqli_query($link, "select torrent_pass from users_main where length(torrent_pass) > 0") or die(mysqli_error($link) . "\n");
while (($row = mysqli_fetch_assoc($result))) {
    $passkeys[] = $row['torrent_pass'];
}

echo "Writing new passkeys to users_main (this will take a while).\n";

$result = mysqli_query($link, "select id from users_main where length(torrent_pass) = 0") or die(mysqli_error($link) . "\n");
$count = 0;
while (($row = mysqli_fetch_assoc($result))) {
    $newpasskey = mysqli_real_escape_string($link, make_secret2());

    // make sure the key is unique.
    while (in_array($newpasskey, $passkeys)) {
        $newpasskey = mysqli_real_escape_string($link, make_secret2());
    }
    $passkeys[] = $newpasskey;

    $passkey_rows[] = "('" . $row['id'] . "', '" . $newpasskey . "')";
    $count++;
    if ($count % 100 == 0) {
        $sql = "INSERT INTO users_main
                    (id, torrent_pass) 
                VALUES " . implode(',', $passkey_rows) .
                " ON DUPLICATE KEY UPDATE torrent_pass=VALUES(torrent_pass)";
        $passkey_rows = array();
        mysqli_query($link, $sql) or die(mysqli_error($link) . "\n");
        echo '.';
    }
}

echo "\n";

// Flush the last ones if any..
if (count($passkey_rows) > 0) {
    $sql = "INSERT INTO users_main
                (id, torrent_pass) 
            VALUES " . implode(',', $passkey_rows) .
            " ON DUPLICATE KEY UPDATE torrent_pass=VALUES(torrent_pass)";
    mysqli_query($link, $sql) or die(mysqli_error($link) . "\n");
}

// we are done.
$time = microtime(true) - $time_start;
echo "Total execution time: $time seconds\n";