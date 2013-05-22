<? 
if(!check_perms('users_view_ips')) { error(403); }

// The "order by x" links on columns headers

/** 
 * 
 * @param $SortKey  'new_id', 'new_name', 'joindate', 'IP', 'b_id', 'b_name', 'bandate'
 * @param $DefaultWay 'desc' or 'asc'
 */
function header_link($SortKey, $DefaultWay = "desc") {
    global $OrderBy, $OrderWay;
    if ($SortKey == $OrderBy) {
        if ($OrderWay == "desc") {
            $NewWay = "asc";
        } else {
            $NewWay = "desc";
        }
    } else {
        $NewWay = $DefaultWay;
    }

    return "tools.php?action=banned&amp;order_way=$NewWay&amp;order_by=$SortKey&amp;" . get_url(array('action', 'order_way', 'order_by'));
}
if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}
 
if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('new_id', 'new_name', 'joindate', 'IP', 'b_id', 'b_name', 'bandate' ))) {
    $_GET['order_by'] = 'joindate';
    $OrderBy = 'joindate'; 
} else {
    $OrderBy = $_GET['order_by'];
}
/* 
 * BanReason 0 - Unknown, 1 - Manual, 2 - Ratio, 3 - Inactive, 4 - Cheating.
 */
$Reasons = array(0=>'Unknown',1=>'Manual',2=>'Ratio',3=>'Inactive',4=>'Cheating' );
$BanReason = (isset($_GET['ban_reason']) && is_number($_GET['ban_reason']) && $_GET['ban_reason'] < 5) ? (int)$_GET['ban_reason'] : 2 ;

$Days =  (isset($_GET['days']) && is_number($_GET['days']) && $_GET['days'] < 5000) ? (int)$_GET['days'] : 14 ;

list($Page,$Limit) = page_limit(50);



$DB->query("SELECT SQL_CALC_FOUND_ROWS
                   nu.ID as new_id, 
                   ni.JoinDate as joindate, 
                   bu.IP as IP, 
                   bu.ID as b_id, 
                   bi.BanDate as bandate, 
                   nu.Username as new_name, 
                   bu.Username as b_name
              FROM users_info as bi 
              JOIN users_main as bu ON bi.UserID=bu.ID AND bu.Enabled='2' 
                    AND bi.Banreason='$BanReason' AND bi.BanDate > NOW() - INTERVAL $Days DAY
              JOIN users_main AS nu ON nu.Enabled='1' AND  nu.ID!=bu.ID AND nu.IP=bu.IP    
              JOIN users_info AS ni ON ni.UserID=nu.ID AND ni.JoinDate>bi.BanDate 
          ORDER BY $OrderBy $OrderWay
             LIMIT $Limit ;");

$DupeRecords = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
 
$Pages=get_pages($Page,$NumResults,50,9);


show_header('Dupe IPs','dupeip');

?>
<div class="thin">
	<h2>Returning Dupe IP's</h2>
	<div class="linkbox">
		<a href="tools.php?action=dupe_ips">[Dupe IP's]</a>
		<strong><a href="tools.php?action=banned_ip_users">[Returning Dupe IP's]</a></strong>
	</div>
    
    
  <? /* 
$Reason = array(0=>'Unknown',1=>'Manual',2=>'Ratio',3=>'Inactive',4=>'Cheating' );
$BanReason = (isset($_GET['ban_reason']) && is_number($_GET['ban_reason']) && $_GET['ban_reason'] < 5) ? (int)$_GET['ban_reason'] : 2 ;

$Days =  (isset($_GET['days']) && is_number($_GET['days']) && $_GET['days'] < 5000) ? (int)$_GET['days'] : 14 ;
 */ ?>
	<div class="head">view settings</div>
    <table width="100%">
        <tr>   
           <td class="colhead center" colspan="2">
                Viewing: banned for <?=$Reasons[$BanReason]?> in the last <?=$Days?> days &nbsp; (order: <?="$OrderBy $OrderWay"?>)
            </td>
        </tr>
        <tr>
            <td class="center">
                <label for="ban_reason" title="View Speed">Ban Reason </label>
                <select id="ban_reason" name="ban_reason" title="" onchange="change_view(<?="'$OrderBy','$OrderWay'"?>)">
<?                  foreach($Reasons as $Key=>$Reason) {   ?>
                        <option value="<?=$Key?>" <?=($Key==$BanReason?' selected="selected"':'');?>>&nbsp;<?=$Reason;?> &nbsp;</option>
<?                  } ?>
                </select>
            </td>
            <td class="center">
                <label for="days" title="include where ban was >= days">banned within days: </label>
                <input type="text" onchange="change_view(<?="'$OrderBy','$OrderWay'"?>)" id="days" name="days"  value="<?=$Days?>" />
            </td>
        </tr>
    </table>
    <br/>
     
	<div class="linkbox"> <?=$Pages; ?> </div>
    
	<div class="head">Current Users with a Dupe IP from a previously banned account</div>
	<table width="100%">
		<tr class="colhead">
			<td class="center"><a href="<?=header_link('new_name') ?>">User</a></td>
            <td class="center"><a href="<?=header_link('joindate') ?>">Join Date</a></td>
            <td class="center"><a href="<?=header_link('IP') ?>">Shared IP</a></td>
            <td class="center"><a href="<?=header_link('b_name') ?>">Banned User</a></td>
            <td class="center"><a href="<?=header_link('bandate') ?>">Banned Date</a></td>
		</tr>
<?
        if($NumResults==0){
?> 
                    <tr class="rowb">
                        <td class="center" colspan="5">no duped users</td>
                    </tr>
<?      } else {
            $i=0;
            foreach ($DupeRecords as $Record) { 
                list($nID, $JoinDate, $IP, $bID, $BanDate) = $Record;
                $Row = ($Row == 'a') ? 'b' : 'a';
                $i++;
                $nInfo = user_info($nID);
                $bInfo = user_info($bID);
?>
                <tr class="row<?=$Row?>">
                    <td><?=format_username($nID, $nInfo['Username'], $nInfo['Donor'], $nInfo['Warned'], $nInfo['Enabled'], $nInfo['PermissionID'], false, false, $nInfo['GroupPermissionID'])?></td>
                    <td class="center"><?=time_diff($JoinDate)?></td>
                    <td><?=display_str($IP)?><span style="float:right;">[<a href="user.php?action=search&amp;ip_history=on&amp;ip=<?=display_str($IP)?>" title="User Search on this IP" target="_blank">S</a>]</span></td>
                    <td><?=format_username($bID, $bInfo['Username'], $bInfo['Donor'], $bInfo['Warned'], $bInfo['Enabled'], $bInfo['PermissionID'], false, false, $bInfo['GroupPermissionID'])?></td>
                    
                    <td class="center"><?=time_diff($BanDate)?></td>
                </tr>
<?          } 
        } 
?>
	</table>
	<div class="linkbox"> <?=$Pages; ?> </div>
</div>
<?
show_footer();
 

 
/*
 * 
 
SELECT nu.ID as newuser_ID, nu.Username as nu_Username, ni.JoinDate, bu.IP as SharedIP, bu.ID as b_ID, bu.Username as b_name, bi.BanDate
FROM users_info as bi 
JOIN users_main as bu ON bi.UserID=bu.ID AND bu.Enabled='2' AND bi.Banreason='2' AND bi.BanDate > NOW() - INTERVAL 12000 DAY
JOIN users_main AS nu ON nu.Enabled='1' AND  nu.ID!=bu.ID AND nu.IP=bu.IP    
JOIN users_info AS ni ON ni.UserID=nu.ID AND ni.JoinDate>bi.BanDate 
ORDER BY ni.JoinDate DESC
LIMIT 400;
 


SELECT bu.ID as b_ID, bu.Username as b_name, bi.BanDate, bu.IP,
nu.ID as n_ID, nu.Username as n_Username, ni.JoinDate  
FROM users_info as bi 
JOIN users_main as bu ON bi.UserID=bu.ID AND bu.Enabled='2' AND bi.Banreason='2' AND bi.BanDate > NOW() - INTERVAL 12000 DAY
JOIN users_main AS nu ON nu.Enabled='1' AND  nu.ID!=bu.ID AND nu.IP=bu.IP    
JOIN users_info AS ni ON ni.UserID=nu.ID AND ni.JoinDate>bi.BanDate 
ORDER BY ni.JoinDate DESC
LIMIT 400;

 


SELECT bu.ID as b_ID, bu.Username as b_name, bi.BanDate, bu.IP,
nu.ID as n_ID, nu.Username as n_Username, ni.JoinDate  
FROM users_info as bi 
JOIN users_main as bu ON bi.UserID=bu.ID AND bu.Enabled='2'
JOIN users_main AS nu ON nu.Enabled='1' AND  nu.ID!=bu.ID AND nu.IP=bu.IP    
JOIN users_info  AS ni ON ni.JoinDate>bi.BanDate AND ni.UserID=nu.ID
WHERE bi.Banreason='2' AND bi.BanDate > NOW() - INTERVAL 200 DAY
ORDER BY ni.JoinDate DESC
LIMIT 400;

 * 
 * 
 */
?>
