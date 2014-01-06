<?php

/*

function hash_sha256($string) {
  if (function_exists('hash')) return hash('sha256', $string, true);
  if (function_exists('mhash')) return mhash(MHASH_SHA256, $string);
  // insert native php implementation of sha256 here
  throw new Exception('Too lazy to fallback when the guy who configured php was lazy too');
}

function encode_btc($btc) {
  $btc = chr($btc['version']).pack('H*', $btc['hash']);
  if (strlen($btc) != 21) return false;
  $cksum = substr(hash_sha256(hash_sha256($btc)), 0, 4);
  return base58_encode($btc.$cksum);
}

function decode_btc($btc) {
  $btc = base58_decode($btc);
  if (strlen($btc) != 25) return false; // invalid
  $version = ord($btc[0]);
  $cksum = substr($btc, -4);
  // checksum is double sha256 (take 4 first bytes of result)
  $good_cksum = substr(hash_sha256(hash_sha256(substr($btc, 0, -4))), 0, 4);
  if ($cksum != $good_cksum) return false;
  return array('version' => $version, 'hash' => bin2hex(substr($btc, 1, 20)));
}

function base58_encode($string) {
  $table = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

  $long_value = gmp_init(bin2hex($string), 16);

  $result = '';
  while(gmp_cmp($long_value, 58) > 0) {
    list($long_value, $mod) = gmp_div_qr($long_value, 58);
    $result .= $table[gmp_intval($mod)];
  }
  $result .= $table[gmp_intval($long_value)];

  for($nPad = 0; $string[$nPad] == "\0"; ++$nPad);

  return str_repeat($table[0], $nPad).strrev($result);
}

function base58_decode($string) {
  $table = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
  static $table_rev = null;
  if (is_null($table_rev)) {
    $table_rev = array();
    for($i=0;$i<58;++$i) $table_rev[$table[$i]]=$i;
  }

  $l = strlen($string);
  $long_value = gmp_init('0');
  for($i=0;$i<$l;++$i) {
    $c=$string[$l-$i-1];
    $long_value = gmp_add($long_value, gmp_mul($table_rev[$c], gmp_pow(58, $i)));
  }

  // php is lacking binary output for gmp
  $res = pack('H*', gmp_strval($long_value, 16));

  for($nPad = 0; $string[$nPad] == $table[0]; ++$nPad);
  return str_repeat("\0", $nPad).$res;
}

*/

function get_donate_deduction($amount_euros) {
    global $DonateLevels;
    $deduct_bytes = 0;
    $DonateLevelsR = array_reverse($DonateLevels, true);
    foreach ($DonateLevelsR as $level=>$rate) {
        if ($amount_euros >= $level ) {
            $deduct_bytes = floor($amount_euros) * $rate * 1024 * 1024 * 1024; // rate per gb
            break;
        }
    }
    return $deduct_bytes;
}



function print_btc_query_now($ID, $eur_rate, $address) {
    //static $bID = 0;
    //++$bID;
?>    
    <span style="font-style: italic;" id="btc_button_<?=$ID?>">waiting...
        <script type="text/javascript">
            setTimeout("CheckAddress('<?=$ID?>','<?=$eur_rate?>','<?=$address?>','6')", <?=(int)($ID*800)?>);
        </script>
    </span>
<? 
}




function print_btc_query_button($eur_rate, $address, $numtransactions=6) {
    static $bID = 0;
    ++$bID; 
    //return '<span id="btc_button_' . $bID . '">
   // <input type="button" onclick="CheckAddress('.$bID.',\''.$address.'\',\''.$numtransactions.'\')" value="query balance" /></span>';
?>    
    <span style="font-style: italic;" id="btc_button_<?=$bID?>">
        <a href="#" onclick="CheckAddress('<?=$bID?>','<?=$eur_rate?>','<?=$address?>','<?=$numtransactions?>');return false;">query balance</a>
    </span>
<?
    return $bID;
}




function validate_btc_address($address) {
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


function check_bitcoin_balance($address, $numtransactions=6) {

    $satoshis = intval(file_get_contents("http://blockchain.info/q/getreceivedbyaddress/{$address}?confirmations={$numtransactions}"));
    $btc = $satoshis / 100000000.0;
    if ($btc > 0) {
        return sprintf('%.8F', $btc);
    } else {
        return '0';
    }
}



function check_bitcoin_activation($address) {
    
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

    
function get_current_btc_rate() {
    global $DB, $Cache;
    
    $rate = $Cache->get_value('eur_bitcoin');
    if ($rate===false){
        $rate = '0';
        $rate = query_eur_rate();
        if(!$rate){ 
            $Cache->cache_value('eur_bitcoin', 0, 60); // one minute
        } else {
            $Cache->cache_value('eur_bitcoin', $rate, 3600); // one hour
        }
        /* $tickerarray = get_ticker_eur();
        if($tickerarray){
            $rate = $tickerarray['vwap'];
            $Cache->cache_value('eur_bitcoin', $rate, 3600); // one hour
        } */
    }
    return $rate;
}




function query_btc_rate_bitstamp($testing=false) {
         /*
        // Fetch the current rate from MtGox
        //echo " $type $geo $currency";
        $ch = curl_init('https://www.bitstamp.net/api/ticker/');
        curl_setopt($ch, CURLOPT_REFERER, 'Mozilla/5.0 (compatible; MtGox PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        curl_setopt($ch, CURLOPT_USERAGENT, 'CakeScript/0.1');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $mtgoxjson = curl_exec($ch);
        curl_close($ch);

        */
        $mtgoxjson = file_get_contents("https://www.bitstamp.net/api/ticker/");
    
        if($testing) return $mtgoxjson;
        
        // Decode from an object to array
        if($mtgoxjson){
            
            $output_mtgox = json_decode($mtgoxjson, true);
            //$output_mtgox_1 = get_object_vars($output_mtgox);
            //$mtgox_array = get_object_vars($output_mtgox_1['ticker']);
            //error( print_r($output_mtgox));
            // something's wrong
            if (!$output_mtgox) {
                return false;
            }
 

            $currencyRate = $output_mtgox['low'];
            
            return $currencyRate;
        }
        return false; 
 
 }


function query_eur_rate($testing=false) {
         
        // Fetch the current rate from MtGox
        /*
        $ch = curl_init('https://data.mtgox.com/api/1/BTCEUR/ticker');
        curl_setopt($ch, CURLOPT_REFERER, 'Mozilla/4.0 (compatible; MtGox PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
        curl_setopt($ch, CURLOPT_USERAGENT, 'CakeScript/0.4');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $mtgoxjson = curl_exec($ch);
        curl_close($ch); */

        $mtgoxjson = file_get_contents("https://data.mtgox.com/api/1/BTCEUR/ticker");
        
        if($testing) return $mtgoxjson;
        
        // Decode from an object to array
        if($mtgoxjson){
            
            $output_mtgox = json_decode($mtgoxjson, true);
            
            // something's wrong
            if (!$output_mtgox OR !isset($output_mtgox['result'])) {
                return false;
            }

            if ($output_mtgox['result'] !== 'success') {
                
                return false;
            }

            $currencyRate = $output_mtgox['return']['vwap']['value'];
            
            return $currencyRate;
        }
        return false; 
 
 }
 

// old version api 0
function get_ticker_eur_v0() {
        
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
        if($mtgoxjson){
            $output_mtgox = json_decode($mtgoxjson);
            $output_mtgox_1 = get_object_vars($output_mtgox);
            $mtgox_array = get_object_vars($output_mtgox_1['ticker']);
            return $mtgox_array;
        }
        return false; 
 
 }
 
 
 
  
 
 
 
 
 
?>
