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
    //return $bID;
    
    /*   CheckAddress('<?=$bID?>','<?=$eur_rate?>','<?=$address?>','6'); */
    //return '<span id="host_' . $ID . '">Resolving host ' . $IP . '...<script type="text/javascript">ajax.get(\'tools.php?action=get_host&ip=' . $IP . '\',function(host){$(\'#host_' . $ID . '\').raw().innerHTML=host;});</script></span>';
 
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
    
    return file_get_contents("http://blockexplorer.com/q/getreceivedbyaddress/$address/$numtransactions");
 
}



function check_bitcoin_activation($address) {
    
    return file_get_contents("http://blockexplorer.com/q/addressfirstseen/$address");  
}




    
function get_current_btc_rate() {
    global $DB, $Cache;
    
    $rate = $Cache->get_value('eur_bitcoin');
    if ($rate==false){
        $rate = '0';
        $tickerarray = get_ticker_eur();
        if($tickerarray){
            $rate = $tickerarray['vwap'];
            $Cache->cache_value('eur_bitcoin', $rate, 3600); // one hour
        }
    }
    return $rate;
}



function get_ticker_eur() {
        
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
        
        /*
        $returndata="";
            
        $last=round ( $mtgox_array['last'], 3);
        $low =round ( $mtgox_array['low'], 3);
        $high=round ( $mtgox_array['high'], 3);
        $vol =round ( $mtgox_array['vol'], 3);
        $avg = round ( $mtgox_array['avg'], 3 );
        $ask = round ( $mtgox_array['sell'], 3 );
        $bid = round ( $mtgox_array['buy'], 3 );
     
         Array
(
    [ticker] => stdClass Object
        (
            [high] => 34.8
            [low] => 31.15337
            [avg] => 33.109894651
            [vwap] => 33.007414675
            [vol] => 8539
            [last_all] => 33.779
            [last_local] => 33.779
            [last] => 33.779
            [buy] => 33.4901
            [sell] => 33.75209
        )

)
        // return "<pre>". print_r($output_mtgox_1, true)."</pre>";
        
        //echo $type;
        if ( $type == "html" )
        {
        $returndata="<ul><li><strong>Last:</strong>&nbsp;&nbsp;".$last."</li><li><strong>High:</strong>&nbsp;".$high."</li><li><strong>Low:</strong>&nbsp;&nbsp;".$low."</li><li><strong>Avg:</strong>&nbsp;&nbsp;&nbsp;".$avg."</li><li><strong>Vol:</strong>&nbsp;&nbsp;&nbsp;&nbsp;".$vol."</li><li><strong>bid:</strong>&nbsp;&nbsp;&nbsp;".$bid."</li><li><strong>ask:</strong>&nbsp;&nbsp;&nbsp;".$ask."</li></ul>";
        //$returndata="<ul><li><strong>Last:</strong>&nbsp;&nbsp;".$mtgox_array['last']."</li><li><strong>High:</strong>&nbsp;".$mtgox_array['high']."</li><li><strong>Low:</strong>&nbsp;&nbsp;".$mtgox_array['low']."</li><li><strong>Avg:</strong>&nbsp;&nbsp;&nbsp;".$mtgox_array['avg']."</li><li><strong>Vol:</strong>&nbsp;&nbsp;&nbsp;&nbsp;".$mtgox_array['vol']."</li></ul>";
        }
        else if ( $type == "text" )
        {
        //echo $geo;
            if ( $geo == "line" )
                $returndata="LAST: ".$last." HIGH: ".$high." LOW: ".$low." AVG: ".$avg." VOL: ".$vol." BID: ".$bid." ASK: ".$ask." ";
            else if ( $geo == "vertical" )
                $returndata="LAST: ".$last."\nHIGH: ".$high."\nLOW : ".$low."\nAVG : ".$avg."\nVOL : ".$vol."\nBID: ".$bid."\nASK: ".$ask;
        }
        return $returndata;
         */
 
 }
 
 
 
  
 
 
 
 
 
?>
