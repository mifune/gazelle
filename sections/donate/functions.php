<?php
function get_donate_deduction($amount_euros)
{
    global $DonateLevels;
    $deduct_bytes = 0;
    $DonateLevelsR = array_reverse($DonateLevels, true);
    foreach ($DonateLevelsR as $level=>$rate) {
        if ($amount_euros >= $level) {
            $deduct_bytes = floor($amount_euros) * $rate * 1024 * 1024 * 1024; // rate per gb
            break;
        }
    }

    return $deduct_bytes;
}

function print_btc_query_now($ID, $eur_rate, $address)
{
?>
    <span style="font-style: italic;" id="btc_button_<?=$ID?>">waiting...
        <script type="text/javascript">
            setTimeout("CheckAddress('<?=$ID?>','<?=$eur_rate?>','<?=$address?>','6')", <?=(int) ($ID*800)?>);
        </script>
    </span>
<?php
}

function print_btc_query_button($eur_rate, $address, $numtransactions=6)
{
    static $bID = 0;
    ++$bID;
?>
    <span style="font-style: italic;" id="btc_button_<?=$bID?>">
        <a href="#" onclick="CheckAddress('<?=$bID?>','<?=$eur_rate?>','<?=$address?>','<?=$numtransactions?>');return false;">query balance</a>
    </span>
<?php

    return $bID;
}

function validate_btc_address($address)
{
    // just do a cursory check on format
    //  /^[a-zA-Z1-9]{27,35}$/
    //  // starts with a 1 or a 3
    //uppercase letter "O", uppercase letter "I",
    //lowercase letter "l", and the number "0" are never used to prevent visual ambiguity.
    if ( preg_match( BTC_ADDRESS_REGEX , $address) ) {
        // could/should do a hash check here to validate the internal checksum but.... meh.
        return true;
    } else {
        return false;
    }
}

function check_bitcoin_balance($address, $numtransactions=6)
{
    $satoshis = intval(file_get_contents("http://blockchain.info/q/getreceivedbyaddress/{$address}?confirmations={$numtransactions}"));
    $btc = $satoshis / 100000000.0;
    if ($btc > 0) {
        return sprintf('%.8F', $btc);
    } else {
        return '0';
    }
}

function check_bitcoin_activation($address)
{
    $timestamp = intval(file_get_contents("http://blockchain.info/q/addressfirstseen/$address"));
    if ($timestamp > 0) {
        return date('Y-m-d H:i:s', $timestamp);
    } else {
        return "Never seen";
    }
}

// http://api.bitcoincharts.com/v1/weighted_prices.json
// https://www.bitstamp.net/api/ticker/
// https://data.mtgox.com/api/1/BTCEUR/ticker

function get_current_btc_rate()
{
    global $DB, $Cache;

    $rate = $Cache->get_value('eur_bitcoin');
    if ($rate===false) {
        $rate = '0';
        $rate = query_eur_rate();
        if (!$rate) {
            $Cache->cache_value('eur_bitcoin', 0, 60); // one minute
        } else {
            $Cache->cache_value('eur_bitcoin', $rate, 3600); // one hour
        }
    }

    return $rate;
}

function query_btc_rate_bitstamp($testing=false)
{
        $mtgoxjson = file_get_contents("https://www.bitstamp.net/api/ticker/");

        if($testing) return $mtgoxjson;

        // Decode from an object to array
        if ($mtgoxjson) {

            $output_mtgox = json_decode($mtgoxjson, true);
            // something's wrong
            if (!$output_mtgox) {
                return false;
            }

            $currencyRate = $output_mtgox['low'];

            return $currencyRate;
        }

        return false;

 }

function query_eur_rate($testing=false)
{
        $coindeskjson = file_get_contents("https://api.coindesk.com/v1/bpi/currentprice/EUR.json");

        if($testing) return $coindeskjson;

        // Decode from an object to array
        if ($coindeskjson) {

            $output_coindesk = json_decode($coindeskjson, true);

            // something's wrong
            if (!$output_coindesk OR !isset($output_coindesk['bpi'])) {
                return false;
            }

            $currencyRate = $output_coindesk['bpi']['EUR']['rate'];

            return $currencyRate;
        }

        return false;

 }

// old version api 0
function get_ticker_eur_v0()
{
        $currency = "EUR";

        // Fetch the current rate from MtGox
        //echo " $type $geo $currency";
        $ch = curl_init('https://data.mtgox.com/api/0/data/ticker.php?Currency='.$currency);
        curl_setopt($ch, CURLOPT_REFERER, 'Mozilla/5.0 (compatible; MtGox PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        curl_setopt($ch, CURLOPT_USERAGENT, "CakeScript/0.1");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $mtgoxjson = curl_exec($ch);
        curl_close($ch);

        // Decode from an object to array
        if ($mtgoxjson) {
            $output_mtgox = json_decode($mtgoxjson);
            $output_mtgox_1 = get_object_vars($output_mtgox);
            $mtgox_array = get_object_vars($output_mtgox_1['ticker']);

            return $mtgox_array;
        }

        return false;
 }
