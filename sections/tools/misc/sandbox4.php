<?

 error("dont press that!"); 

$filename = "peersid.txt"; 
 
$input = file($filename);

$i=0;

echo count($input)."<br/>";
 
$Values = array();
$fname=1;

$Peers = array();

foreach($input as $key=>$line){
 
    if (strpos($line, "INSERT INTO")!==FALSE){
 
    } else {
        $parts = explode("', '", $line);
        if (count($parts)>1) {
            $parts[0] = substr($parts[0], 2, 8);
            if (substr($parts[0], 0, 4)!='exbc'){
                $str = preg_replace('/[^a-z0-9]/', '', $parts[0]);
                if ( !array_key_exists($str, $Peers)){
                    $i++;
                    $parts[1] = substr($parts[1], 0, strlen($parts[1])-4 );
                    //$Values[] = "('$parts[0]', '$parts[1]'),\n";
                    //$Peers[$str] = array(0, "('$parts[0]', '$parts[1]'),\n");
                    $Peers[$str] = array(0, db_string($parts[0]) , $parts[1] );
                } else {
                    $Peers[$str][0]=$Peers[$str][0]+1;
                }
            
            }
        } 
    } 
}


foreach($Peers as $key=>$val){
                    //$Peers[$str] = array(0, "('$parts[0]', '$parts[1]'),\n");
    $Values[] = "('$val[0]', '$val[1]', '$val[2]'),\n";
}

            if (!saverest( "/home/mifune/www/gazelle/peersid_ALL.sql", $Values)) {
                echo "Error<br/>";
                break;
            }


echo $i;
 

function saverest($filename, $output){
    
    //$filename = 'peersid2.txt';
    //$somecontent = "Add this to the file\n";
    $handle = fopen($filename, 'w');
    
        if ( $handle === FALSE) {
             echo "Cannot open file ($filename)<br/>";
             exit;
        }
        
    // Let's make sure the file exists and is writable first.
    if (is_writable($filename)) {

        // In our example we're opening $filename in append mode.
        // The file pointer is at the bottom of the file hence
        // that's where $somecontent will go when we fwrite() it.

        $output = implode("", $output);
        $output = substr($output,0, strlen($output)-2).";\n";
        // Write $somecontent to our opened file.
        if (fwrite($handle, $output) === FALSE) {
            echo "Cannot write to file ($filename)<br/>";
            exit;
        }

        echo "Success, wrote input to file ($filename)<br/>";

        fclose($handle);
        return true;
    } else {
        echo "The file $filename is not writable<br/>";
        return false;
    }
}
 
?>


