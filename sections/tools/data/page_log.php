<?php
enforce_login();

if (!check_perms('admin_manage_site_options')) {
    error(403);
}

// The "order by x" links on columns headers
function header_link($SortKey, $DefaultWay = "desc")
{
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

    return "tools.php?action=page_log&amp;order_way=$NewWay&amp;order_by=$SortKey&amp;" . get_url(array('action', 'order_way', 'order_by'));
}

define('PAGELOG_ENTRIES_PER_PAGE', 100);

list($Page,$Limit) = page_limit(PAGELOG_ENTRIES_PER_PAGE);

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc'; // For header links
} else {
    $_GET['order_way'] = 'desc';
    $OrderWay = 'desc';
}

if (empty($_GET['order_by']) || !in_array($_GET['order_by'], array('id', 'userid', 'time', 'ipnum', 'request', 'variables'  ))) {
    $_GET['order_by'] = 'id';
    $OrderBy = 'id';
} else {
    $OrderBy = $_GET['order_by'];
}

show_header('Page Logging' );

$DB->query("SELECT SQL_CALC_FOUND_ROWS
                   id, userid, time, ip, request, variables
              FROM full_log
          ORDER BY $OrderBy $OrderWay
             LIMIT $Limit ");
$Results = $DB->to_array();
$DB->query("SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();

?>
<div class="thin">
    <h2>Page requests log</h2>

    <div class="linkbox">
<?php
    $Pages=get_pages($Page,$NumResults,PAGELOG_ENTRIES_PER_PAGE,9);
    echo $Pages;
?>
    </div>
    <div class="head">Logging options</div>
    <div class="box">
        <form action="tools.php" method="post">
            <input type="hidden" name="action" value="change_logging" />
            <input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
            <table class="shadow">
                <tr>
                    <td class="label">Page Request Logging:</td>
                    <td colspan="3">
                        <select name="logging">
                            <option value="0" <?php  if ($FullLogging=='0') echo 'selected="selected"' ;?>>Off</option>
                            <option value="1" <?php  if ($FullLogging=='1') echo 'selected="selected"' ;?>>Only user.php</option>
                            <option value="2" <?php  if ($FullLogging=='2') echo 'selected="selected"' ;?>>Nearly All (excludes some common known ajax calls)</option>
                            <option value="3" <?php  if ($FullLogging=='3') echo 'selected="selected"' ;?>>Absolutely All</option>
                        </select>
                        <input type="submit" name="submit" value="Change logging status" />
                    </td>
                </tr>
                <tr>
                    <td class="label">Delete logs:</td>
                    <td>
                        <input type="submit" name="submit" value="Delete all" />
                    </td>
                    <td class="label"> </td>
                    <td class="center">
                        logs with id &nbsp;&nbsp;
                        <input type="checkbox" name="id_under" value="1" /> under <input type="text" name="under" size="3" /> &nbsp;&nbsp;
                        <input type="checkbox" name="id_over"  value="1" /> over <input type="text" name="over" size="3" />
                        <input type="submit" name="submit" value="Delete some" />
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <div class="head"><?=str_plural('page log', $NumResults)?> <span style="float:right">show/hide POST vars <input type="checkbox" onclick="$('.vars').toggle()"/></span></div>
    <table class="border" width="100%">
        <tr class="colhead">
            <td style="width: 20px;"><a href="<?=header_link('id') ?>">id</a></td>
            <td><a href="<?=header_link('userid') ?>">User</a></td>
            <td><a href="<?=header_link('time') ?>">Time</a></td>
            <td><a href="<?=header_link('ipnum') ?>">IP</a></td>
            <td><a href="<?=header_link('variables') ?>">type</a></td>
            <td><a href="<?=header_link('request') ?>">Request</a></td>
        </tr>

<?php
    foreach ($Results as $record) {
        list($id, $userid, $time, $ip, $request, $variables) = $record;
        $Row = ($Row == 'a') ? 'b' : 'a';
        $parts = explode("~", $variables);
        if ($parts[0]=='POST' && isset($parts[2])) {
            $keys = explode("|", $parts[1]);
            $values = explode("|", $parts[2]);
            $vars = array();
            foreach ($keys as $index=>$key) {
                $vars[] = "$key={$values[$index]}";
            }
            $vars = '<span class="vars hidden" style="color:grey"><br/>'. implode(" | ", $vars).'</span>';
        } else {
            $vars='';
        }
        $UserInfo = user_info($userid);
?>
        <tr class="row<?=$Row?>">
            <td class="nobr"><span style="color:grey"><?=$id?></span></td>
            <td class="nobr"><?=("$userid &nbsp;".format_username($userid,$UserInfo['Username']))?></td>
            <td class="nobr"><?=time_diff($time,2,true,false,1)?></td>
            <td class="nobr"><?=$ip?></td>
            <td class="nobr"><?=$parts[0]?></td>
            <td class=""><?=$request?><?=$vars?></td>
        </tr>
<?php
    }
?>
    </table>

    <div class="linkbox">
        <?=$Pages?>
    </div>
</div>
<?php
show_footer();
