
<?
/*
for($fid=1;$fid<=25;$fid++){
    
    $DB->query("INSERT INTO `xbt_files_users`(`uid`, `active`, `announced`, `completed`, `downloaded`, `remaining`, `uploaded`, `upspeed`, `downspeed`, `corrupt`, `timespent`, `useragent`, `connectable`, `peer_id`, `fid`, `mtime`, `ip`)
            SELECT ID, '1', '1', '1','1','0','1','1','1','1', '1','1','0',ID,'$fid','1','1' FROM users_main WHERE ID<50000");

}

$DB->query("SELECT COUNT(*) FROM xbt_files_users ");
list($count)= $DB->next_record();

echo "did $fid loops: $count records in xbt_files_users";
*/

       // $num = round( ( sqrt( 8.0 * ( $count / 20  ) + 1.0 ) - 1.0  )  / 2.0 *20 );


//echo "dont press that!<br/>";
$a = isset($_REQUEST['a'])?$_REQUEST['a']:0.74;
$b = isset($_REQUEST['b'])?$_REQUEST['b']:2;
$c = isset($_REQUEST['c'])?$_REQUEST['c']:6.5;
$d = isset($_REQUEST['d'])?$_REQUEST['d']:0;
$cap = isset($_REQUEST['cap'])?$_REQUEST['cap']:0;

include(SERVER_ROOT.'/classes/class_text.php');
$Text = new TEXT;

show_header();

?>
<div class="thin">
    <h2>sandbox 1</h2>
        <table style="width:100%">
            <tr>
                <td colspan="3" class="center">
                    <h3>round( ( sqrt( ( <span style="color:red">a</span> * count ) + <span style="color:red">b</span> ) - 1.0  ) *<span style="color:red">c</span> ) + <span style="color:red">d</span> </h3>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="sandbox1" />
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="a" >a:</label>
                        <input size="6" type="text" name="a" value="<?=$a?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="b" >b:</label>
                        <input size="6" type="text" name="b" value="<?=$b?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="c" >c:</label>
                        <input size="6" type="text" name="c" value="<?=$c?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="d" >d:</label>
                        <input size="6" type="text" name="d" value="<?=$d?>" />
                        &nbsp;&nbsp;&nbsp;&nbsp;<label for="cap" >cap:</label>
                        <input size="6" type="text" name="cap" value="<?=$cap?>" />
                        <input type="submit" value="calculate"/>
                    </form><br/>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top">
                    <h4>your formula</h4>
                    <?=$Text->full_format( get_seed_values($cap, $a, $b, $c, $d));?>
                </td>
                <td  style="vertical-align: top">
                    <h4>current formula</h4>
                    <?=$Text->full_format( get_seed_values(0, 2/5, 1, 10));?>
                </td>
                <td  style="vertical-align: top">
                    <h4>new formula</h4>
                    <?=$Text->full_format( get_seed_values(200, 0.21, 1, 16, 1));?>
                    <? //$Text->full_format( get_seed_values(0.74, 2, 6.5, 0));?>
                    <? //$Text->full_format( get_seed_values(4, 3, 2, 1));?>
                </td>
            </tr>
        </table>
 
</div>
     
<?

        show_footer();
        
        

function get_bonus_points($cap, $count, $a = 8.0, $b = 1, $c=10, $d=0){
    
    ////$num = round( ( sqrt( $a * ( $count / $b  ) + 1.0 ) - 1.0  ) *$c ); 
    //$num = round( ( sqrt( ( $a *  $count * $b  ) + 1.0 ) - 1.0  ) *$c );
     
    if ($cap>0 && $count>$cap)$count=$cap;
                
    $num = round( ( sqrt( ( $a *  $count ) + $b ) - 1.0  ) *$c )+$d;
    
    return $num;
}


            //   ROUND( ( SQRT( 8.0 * ( COUNT( * ) /20 ) + 1.0 ) - 1.0 ) / 2.0 *20 ) 

function get_seed_values($cap, $a = 8.0, $b = 1, $c=10, $d=0) {
   //$b = 1 / $b;
    $capm = $cap > 0 ? $cap : "no limit";
    
    $ret = "[size=1]round( ( sqrt( ( $a * count ) + $b ) - 1.0  ) *$c ) + $d\nmax torrents counted per hour = $capm [/size]\n\n";
    $ret .= "[code]";
    $ret .= "+----------+---------+---------+\n";
    $ret .= "| torrents | credits | max 1wk |\n";
    $ret .= "+----------+---------+---------+\n";
    $lastnum=-1;
    for($count=1;$count<= 50;$count++){
            //$num = round( ( sqrt( $a * ( $count / $b  ) + 1.0 ) - 1.0  ) *$c );
            
            $num = get_bonus_points($cap, $count, $a, $b, $c, $d);
            if ($count==10 || $count==20 || $count==30 || $count==40 || $count==50 || $lastnum !== $num) {
                $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." | ". str_pad($num*168, 7)." |\n"; 
                $lastnum=$num;
            }
    }
    
    for($i=0;$i<=20000;$i++){

        if ($i <= 15)
            $count = 50 + ( $i * 10);
        elseif ($i <= 35)
            $count = -175 + ( $i * 25);
        elseif ($i <= 45)
            $count = -2800 + ( $i * 100);
 
        $num = get_bonus_points($cap, $count, $a, $b, $c, $d);
        
        if ($lastnum != $num) {
            $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." | ". str_pad($num*168, 7)." |\n"; 
            $lastnum=$num;
        }
        if ($count >= 1000) break;
    } 
/*
    $count=5000; 
    $num = get_bonus_points($count, $a, $b, $c, $d);
    $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." |\n"; 

    $count=10000; 
    $num = get_bonus_points($count, $a, $b, $c, $d);
    $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." |\n"; 

    $count=15000;
    $num = get_bonus_points($count, $a, $b, $c, $d);
    $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." |\n"; 

    $count=20000;
    $num = get_bonus_points($count, $a, $b, $c, $d);
    $ret .= "| " . str_pad($count, 8)." | ". str_pad($num, 7)." |\n"; 
*/
    $ret .= "+----------+---------+---------+\n";
    $ret .="[/code]\n";
    
    return $ret; 
}

?>