<?php
 
if (!check_perms('site_view_stats')) error(403);

// helper tool for building old data from exisitng dataset (for switchover) NOTE: this is tailored to our specific data... 
if(isset($_POST['builddata']) && check_perms('site_debug')){
    
    $date = new DateTime('2011-02-01');
    //$end = new DateTime("2012-08-25");
    
    $deleteend = date('Y-m-d H:i:s', strtotime( "$_POST[year]-$_POST[month]-$_POST[day]" )  ); 
    if($deleteend===false) error("Error in End date input");
    if (strtotime($deleteend)<strtotime("2011-02-01")) error("End date is before data range ($deleteend < 2011-02-01)");
    //if (strtotime($deleteend)>time()) $deleteend = sqltime();
    
    $end = new DateTime($deleteend);
    
    $DB->query("DELETE FROM site_stats_history WHERE TimeAdded <= '".$end->format('Y-m-d H:i:s')."'");
    
    do {  
        $time = $date->format('Y-m-d H:i:s');
        $lastaccess = date('Y-m-d H:i:s', $date->getTimestamp() - (3600*24*30*4));
        
        /*
        $DB->query("INSERT INTO site_stats_history ( TimeAdded, Users, Torrents, Seeders, Leechers )
                       VALUES ('$time', 
                                (SELECT Count(UserID) AS Users FROM users_info AS u JOIN users_main AS um ON um.ID=u.UserID
                                    WHERE JoinDate < '$time' AND LastAccess > '$lastaccess' 
                                      AND ( BanDate > '$time' OR BanDate='0000-00-00 00:00:00' ) ),
                                (SELECT Count(ID) AS NumT FROM torrents WHERE Time < '$time'),
                                '0','0' )"); */
        
        $DB->query("INSERT INTO site_stats_history ( TimeAdded, Users, Torrents, Seeders, Leechers )
                       VALUES ('$time', 
                                (SELECT Count(UserID) AS Users FROM users_info AS u JOIN users_main AS um ON um.ID=u.UserID
                                    WHERE JoinDate < '$time' AND Enabled='1' 
                                      AND ( BanDate > '$time' OR BanDate='0000-00-00 00:00:00' ) ),
                                (SELECT Count(ID) AS NumT FROM torrents WHERE Time < '$time'),
                                '0','0' )");
        $date->add(new DateInterval('PT6H'));
        if ($date>$end) break;
    } while(true);
    
    $Cache->delete_value('site_stats');
}


if (isset($_POST['view']) && check_perms('site_stats_advanced')) {
    
    $start = date('Y-m-d H:i:s', strtotime( "$_POST[year1]-$_POST[month1]-$_POST[day1]" )  );
    $end = date('Y-m-d H:i:s', strtotime( "$_POST[year2]-$_POST[month2]-$_POST[day2]" )  ); 
   // error("$start --> $end");
    if($start===false) error("Error in start time input");
    if($end===false) error("Error in end time input");
    if (strtotime($start)<strtotime("2011-02-01")) {
        $start = "2011-02-01 00:00:00";
        $_POST[year1]=2011; $_POST[month1]=02; $_POST[day1]=01;
    }
    if (strtotime($end)>time()) $end = sqltime();
    if ($start>=$end) error("Start date ($start) cannot be after end date ($end)");
    
    $DB->query("SELECT DATE_FORMAT(TimeAdded,'%d %b %y') AS Label, 
                       CAST(AVG(Users) AS SIGNED) AS Users, 
                       CAST(AVG(Torrents) AS SIGNED) AS Torrents, 
                       CAST(AVG(Seeders) AS SIGNED) AS Seeders, 
                       CAST(AVG(Leechers) AS SIGNED) AS Leechers
                    FROM site_stats_history 
                   WHERE TimeAdded >= '$start' AND TimeAdded <= '$end'
                GROUP BY DATE_FORMAT(TimeAdded,'%d %b %y')
                  HAVING Count(ID)=4
                ORDER BY TimeAdded DESC");
    $title = "$_POST[year1]-$_POST[month1]-$_POST[day1] to $_POST[year2]-$_POST[month2]-$_POST[day2]";

    $SiteStats = array_reverse($DB->to_array());
    
}

if (!$SiteStats) {
    $SiteStats = $Cache->get_value('site_stats');
    $title = "last 365 days";
}
if ($SiteStats === false) {
 
    $DB->query("SELECT DATE_FORMAT(TimeAdded,'%d %b %y') AS Label, 
                       CAST(AVG(Users) AS SIGNED) AS Users, 
                       CAST(AVG(Torrents) AS SIGNED) AS Torrents, 
                       CAST(AVG(Seeders) AS SIGNED) AS Seeders, 
                       CAST(AVG(Leechers) AS SIGNED) AS Leechers
                    FROM site_stats_history 
                GROUP BY DATE_FORMAT(TimeAdded,'%d %b %y')
                  HAVING Count(ID)=4
                ORDER BY TimeAdded DESC
                   LIMIT 365");
    $title = "last 365 days";
 
    $SiteStats = array_reverse($DB->to_array());
    //error(print_r($SiteStats,true));
    $Cache->cache_value('site_stats',$SiteStats, 3600*12 ); 
}

$startrow=0;
$endrow=0;
$maxrows = count($SiteStats)-1;

if (count($SiteStats)>0) {
 
    $endrow= $maxrows;
    $cols = "cols: [{id: 'date', label: 'Date', type: 'string'},
                    {id: 'users', label: 'Users', type: 'number'},
                    {id: 'torrents', label: 'Torrents', type: 'number'},
                    {id: 'seeders', label: 'Seeders', type: 'number'},
                    {id: 'leechers', label: 'Leechers', type: 'number'}] ";
     
    $rows = array();        
    //reset($SiteStats);
    foreach ($SiteStats as $data)  {  
        list($Label, $Users, $Torrents, $Seeders, $Leechers) = $data;
        $rows[] = " {c:[{v: '$Label'}, {v: $Users}, {v: $Torrents}, {v: $Seeders}, {v: $Leechers}]} ";
    }
    $data = " { $cols, rows: [" . implode(",", $rows) . "] }"; 
}

show_header('Site Statistics', 'charts,jquery');

?>
<div class="thin">
    <div class="head">Site statistics</div>
    <div class="box pad center">
<?
    if ($data) {
        //$data = "{ cols: [{id: 'date', label: 'Date', type: 'string'}, {id: 'users', label: 'Users', type: 'number'}, {id: 'torrents', label: 'Torrents', type: 'number'}, {id: 'seeders', label: 'Seeders', type: 'number'}, {id: 'leechers', label: 'Leechers', type: 'number'}] , rows: [ {c:[{v: '01 Jan 12'}, {v: 38510}, {v: 5830}, {v: 0}, {v: 0}]} , {c:[{v: '02 Jan 12'}, {v: 38639}, {v: 5840}, {v: 0}, {v: 0}]} , {c:[{v: '03 Jan 12'}, {v: 38762}, {v: 5850}, {v: 0}, {v: 0}]} , {c:[{v: '04 Jan 12'}, {v: 38870}, {v: 5862}, {v: 0}, {v: 0}]} , {c:[{v: '05 Jan 12'}, {v: 38971}, {v: 5874}, {v: 0}, {v: 0}]} , {c:[{v: '06 Jan 12'}, {v: 39057}, {v: 5889}, {v: 0}, {v: 0}]} , {c:[{v: '07 Jan 12'}, {v: 39152}, {v: 5900}, {v: 0}, {v: 0}]} , {c:[{v: '08 Jan 12'}, {v: 39260}, {v: 5910}, {v: 0}, {v: 0}]} , {c:[{v: '09 Jan 12'}, {v: 39398}, {v: 5916}, {v: 0}, {v: 0}]} , {c:[{v: '10 Jan 12'}, {v: 39524}, {v: 5932}, {v: 0}, {v: 0}]} , {c:[{v: '11 Jan 12'}, {v: 39622}, {v: 5944}, {v: 0}, {v: 0}]} , {c:[{v: '12 Jan 12'}, {v: 39703}, {v: 5954}, {v: 0}, {v: 0}]} , {c:[{v: '13 Jan 12'}, {v: 39779}, {v: 5967}, {v: 0}, {v: 0}]} , {c:[{v: '14 Jan 12'}, {v: 39876}, {v: 5990}, {v: 0}, {v: 0}]} , {c:[{v: '15 Jan 12'}, {v: 39963}, {v: 6008}, {v: 0}, {v: 0}]} , {c:[{v: '16 Jan 12'}, {v: 40063}, {v: 6020}, {v: 0}, {v: 0}]} , {c:[{v: '17 Jan 12'}, {v: 40155}, {v: 6044}, {v: 0}, {v: 0}]} , {c:[{v: '18 Jan 12'}, {v: 40245}, {v: 6054}, {v: 0}, {v: 0}]} , {c:[{v: '19 Jan 12'}, {v: 40339}, {v: 6062}, {v: 0}, {v: 0}]} , {c:[{v: '20 Jan 12'}, {v: 40420}, {v: 6071}, {v: 0}, {v: 0}]} , {c:[{v: '21 Jan 12'}, {v: 40521}, {v: 6094}, {v: 0}, {v: 0}]} , {c:[{v: '22 Jan 12'}, {v: 40632}, {v: 6108}, {v: 0}, {v: 0}]} , {c:[{v: '23 Jan 12'}, {v: 40765}, {v: 6112}, {v: 0}, {v: 0}]} , {c:[{v: '24 Jan 12'}, {v: 40929}, {v: 6128}, {v: 0}, {v: 0}]} , {c:[{v: '25 Jan 12'}, {v: 41093}, {v: 6144}, {v: 0}, {v: 0}]} , {c:[{v: '26 Jan 12'}, {v: 41257}, {v: 6168}, {v: 0}, {v: 0}]} , {c:[{v: '27 Jan 12'}, {v: 41408}, {v: 6187}, {v: 0}, {v: 0}]} , {c:[{v: '28 Jan 12'}, {v: 41628}, {v: 6200}, {v: 0}, {v: 0}]} , {c:[{v: '29 Jan 12'}, {v: 41856}, {v: 6215}, {v: 0}, {v: 0}]} , {c:[{v: '30 Jan 12'}, {v: 42104}, {v: 6236}, {v: 0}, {v: 0}]} , {c:[{v: '31 Jan 12'}, {v: 49687}, {v: 6319}, {v: 0}, {v: 0}]} , {c:[{v: '01 Feb 12'}, {v: 57243}, {v: 6607}, {v: 0}, {v: 0}]} , {c:[{v: '02 Feb 12'}, {v: 61226}, {v: 6926}, {v: 0}, {v: 0}]} , {c:[{v: '03 Feb 12'}, {v: 64946}, {v: 7214}, {v: 0}, {v: 0}]} , {c:[{v: '04 Feb 12'}, {v: 67966}, {v: 7503}, {v: 0}, {v: 0}]} , {c:[{v: '05 Feb 12'}, {v: 70803}, {v: 7848}, {v: 0}, {v: 0}]} , {c:[{v: '06 Feb 12'}, {v: 73289}, {v: 8211}, {v: 0}, {v: 0}]} , {c:[{v: '07 Feb 12'}, {v: 75290}, {v: 8537}, {v: 0}, {v: 0}]} , {c:[{v: '08 Feb 12'}, {v: 76815}, {v: 8864}, {v: 0}, {v: 0}]} , {c:[{v: '09 Feb 12'}, {v: 78089}, {v: 9115}, {v: 0}, {v: 0}]} , {c:[{v: '10 Feb 12'}, {v: 79417}, {v: 9372}, {v: 0}, {v: 0}]} , {c:[{v: '11 Feb 12'}, {v: 80687}, {v: 9631}, {v: 0}, {v: 0}]} , {c:[{v: '12 Feb 12'}, {v: 82178}, {v: 9926}, {v: 0}, {v: 0}]} , {c:[{v: '13 Feb 12'}, {v: 83512}, {v: 10223}, {v: 0}, {v: 0}]} , {c:[{v: '14 Feb 12'}, {v: 84516}, {v: 10454}, {v: 0}, {v: 0}]} , {c:[{v: '15 Feb 12'}, {v: 85454}, {v: 10691}, {v: 0}, {v: 0}]} , {c:[{v: '16 Feb 12'}, {v: 86354}, {v: 10903}, {v: 0}, {v: 0}]} , {c:[{v: '17 Feb 12'}, {v: 87182}, {v: 11121}, {v: 0}, {v: 0}]} , {c:[{v: '18 Feb 12'}, {v: 88037}, {v: 11340}, {v: 0}, {v: 0}]} , {c:[{v: '19 Feb 12'}, {v: 88919}, {v: 11598}, {v: 0}, {v: 0}]} , {c:[{v: '20 Feb 12'}, {v: 89870}, {v: 11853}, {v: 0}, {v: 0}]} , {c:[{v: '21 Feb 12'}, {v: 90733}, {v: 12094}, {v: 0}, {v: 0}]} , {c:[{v: '22 Feb 12'}, {v: 91462}, {v: 12292}, {v: 0}, {v: 0}]} , {c:[{v: '23 Feb 12'}, {v: 91937}, {v: 12449}, {v: 0}, {v: 0}]} , {c:[{v: '24 Feb 12'}, {v: 92494}, {v: 12634}, {v: 0}, {v: 0}]} , {c:[{v: '25 Feb 12'}, {v: 93121}, {v: 12828}, {v: 0}, {v: 0}]} , {c:[{v: '26 Feb 12'}, {v: 93783}, {v: 13017}, {v: 0}, {v: 0}]} , {c:[{v: '27 Feb 12'}, {v: 94383}, {v: 13192}, {v: 0}, {v: 0}]} , {c:[{v: '28 Feb 12'}, {v: 94894}, {v: 13330}, {v: 0}, {v: 0}]} , {c:[{v: '29 Feb 12'}, {v: 95364}, {v: 13490}, {v: 0}, {v: 0}]} , {c:[{v: '01 Mar 12'}, {v: 95836}, {v: 13670}, {v: 0}, {v: 0}]} , {c:[{v: '02 Mar 12'}, {v: 96279}, {v: 13876}, {v: 0}, {v: 0}]} , {c:[{v: '03 Mar 12'}, {v: 96751}, {v: 14070}, {v: 0}, {v: 0}]} , {c:[{v: '04 Mar 12'}, {v: 97277}, {v: 14264}, {v: 0}, {v: 0}]} , {c:[{v: '05 Mar 12'}, {v: 97897}, {v: 14426}, {v: 0}, {v: 0}]} , {c:[{v: '06 Mar 12'}, {v: 98402}, {v: 14580}, {v: 0}, {v: 0}]} , {c:[{v: '07 Mar 12'}, {v: 98854}, {v: 14738}, {v: 0}, {v: 0}]} , {c:[{v: '08 Mar 12'}, {v: 99246}, {v: 14876}, {v: 0}, {v: 0}]} , {c:[{v: '09 Mar 12'}, {v: 99761}, {v: 15026}, {v: 0}, {v: 0}]} , {c:[{v: '10 Mar 12'}, {v: 100377}, {v: 15166}, {v: 0}, {v: 0}]} , {c:[{v: '11 Mar 12'}, {v: 101018}, {v: 15350}, {v: 0}, {v: 0}]} , {c:[{v: '12 Mar 12'}, {v: 101679}, {v: 15495}, {v: 0}, {v: 0}]} , {c:[{v: '13 Mar 12'}, {v: 102384}, {v: 15642}, {v: 0}, {v: 0}]} , {c:[{v: '14 Mar 12'}, {v: 103135}, {v: 15746}, {v: 0}, {v: 0}]} , {c:[{v: '15 Mar 12'}, {v: 103789}, {v: 15868}, {v: 0}, {v: 0}]} , {c:[{v: '16 Mar 12'}, {v: 104391}, {v: 16001}, {v: 0}, {v: 0}]} , {c:[{v: '17 Mar 12'}, {v: 105002}, {v: 16161}, {v: 0}, {v: 0}]} , {c:[{v: '18 Mar 12'}, {v: 105640}, {v: 16324}, {v: 0}, {v: 0}]} , {c:[{v: '19 Mar 12'}, {v: 106333}, {v: 16482}, {v: 0}, {v: 0}]} , {c:[{v: '20 Mar 12'}, {v: 106953}, {v: 16612}, {v: 0}, {v: 0}]} , {c:[{v: '21 Mar 12'}, {v: 107524}, {v: 16735}, {v: 0}, {v: 0}]} , {c:[{v: '22 Mar 12'}, {v: 108031}, {v: 16871}, {v: 0}, {v: 0}]} , {c:[{v: '23 Mar 12'}, {v: 108581}, {v: 17003}, {v: 0}, {v: 0}]} , {c:[{v: '24 Mar 12'}, {v: 109098}, {v: 17118}, {v: 0}, {v: 0}]} , {c:[{v: '25 Mar 12'}, {v: 109725}, {v: 17265}, {v: 0}, {v: 0}]} , {c:[{v: '26 Mar 12'}, {v: 110292}, {v: 17431}, {v: 0}, {v: 0}]} , {c:[{v: '27 Mar 12'}, {v: 110774}, {v: 17572}, {v: 0}, {v: 0}]} , {c:[{v: '28 Mar 12'}, {v: 111220}, {v: 17694}, {v: 0}, {v: 0}]} , {c:[{v: '29 Mar 12'}, {v: 111708}, {v: 17818}, {v: 0}, {v: 0}]} , {c:[{v: '30 Mar 12'}, {v: 112197}, {v: 17954}, {v: 0}, {v: 0}]} , {c:[{v: '31 Mar 12'}, {v: 112717}, {v: 18088}, {v: 0}, {v: 0}]} , {c:[{v: '01 Apr 12'}, {v: 113240}, {v: 18228}, {v: 0}, {v: 0}]} , {c:[{v: '02 Apr 12'}, {v: 113770}, {v: 18364}, {v: 0}, {v: 0}]} , {c:[{v: '03 Apr 12'}, {v: 114243}, {v: 18485}, {v: 0}, {v: 0}]} , {c:[{v: '04 Apr 12'}, {v: 114723}, {v: 18644}, {v: 0}, {v: 0}]} , {c:[{v: '05 Apr 12'}, {v: 115211}, {v: 18808}, {v: 0}, {v: 0}]} , {c:[{v: '06 Apr 12'}, {v: 115714}, {v: 18937}, {v: 0}, {v: 0}]} , {c:[{v: '07 Apr 12'}, {v: 116184}, {v: 19087}, {v: 0}, {v: 0}]} , {c:[{v: '08 Apr 12'}, {v: 116675}, {v: 19236}, {v: 0}, {v: 0}]} , {c:[{v: '09 Apr 12'}, {v: 117216}, {v: 19390}, {v: 0}, {v: 0}]} , {c:[{v: '10 Apr 12'}, {v: 117704}, {v: 19521}, {v: 0}, {v: 0}]} , {c:[{v: '11 Apr 12'}, {v: 118226}, {v: 19652}, {v: 0}, {v: 0}]} , {c:[{v: '12 Apr 12'}, {v: 118670}, {v: 19752}, {v: 0}, {v: 0}]} , {c:[{v: '13 Apr 12'}, {v: 119114}, {v: 19858}, {v: 0}, {v: 0}]} , {c:[{v: '14 Apr 12'}, {v: 119602}, {v: 19977}, {v: 0}, {v: 0}]} , {c:[{v: '15 Apr 12'}, {v: 120190}, {v: 20105}, {v: 0}, {v: 0}]} , {c:[{v: '16 Apr 12'}, {v: 120727}, {v: 20243}, {v: 0}, {v: 0}]} , {c:[{v: '17 Apr 12'}, {v: 121204}, {v: 20369}, {v: 0}, {v: 0}]} , {c:[{v: '18 Apr 12'}, {v: 121630}, {v: 20475}, {v: 0}, {v: 0}]} , {c:[{v: '19 Apr 12'}, {v: 122030}, {v: 20576}, {v: 0}, {v: 0}]} , {c:[{v: '20 Apr 12'}, {v: 122435}, {v: 20694}, {v: 0}, {v: 0}]} , {c:[{v: '21 Apr 12'}, {v: 122892}, {v: 20806}, {v: 0}, {v: 0}]} , {c:[{v: '22 Apr 12'}, {v: 123412}, {v: 20932}, {v: 0}, {v: 0}]} , {c:[{v: '23 Apr 12'}, {v: 123888}, {v: 21072}, {v: 0}, {v: 0}]} , {c:[{v: '24 Apr 12'}, {v: 124327}, {v: 21191}, {v: 0}, {v: 0}]} , {c:[{v: '25 Apr 12'}, {v: 124756}, {v: 21319}, {v: 0}, {v: 0}]} , {c:[{v: '26 Apr 12'}, {v: 125165}, {v: 21466}, {v: 0}, {v: 0}]} , {c:[{v: '27 Apr 12'}, {v: 125612}, {v: 21598}, {v: 0}, {v: 0}]} , {c:[{v: '28 Apr 12'}, {v: 126074}, {v: 21714}, {v: 0}, {v: 0}]} , {c:[{v: '29 Apr 12'}, {v: 126558}, {v: 21865}, {v: 0}, {v: 0}]} , {c:[{v: '30 Apr 12'}, {v: 127022}, {v: 22027}, {v: 0}, {v: 0}]} , {c:[{v: '01 May 12'}, {v: 127454}, {v: 22141}, {v: 0}, {v: 0}]} , {c:[{v: '02 May 12'}, {v: 127777}, {v: 22252}, {v: 0}, {v: 0}]} , {c:[{v: '03 May 12'}, {v: 128198}, {v: 22372}, {v: 0}, {v: 0}]} , {c:[{v: '04 May 12'}, {v: 128672}, {v: 22488}, {v: 0}, {v: 0}]} , {c:[{v: '05 May 12'}, {v: 129110}, {v: 22616}, {v: 0}, {v: 0}]} , {c:[{v: '06 May 12'}, {v: 129624}, {v: 22745}, {v: 0}, {v: 0}]} , {c:[{v: '07 May 12'}, {v: 130110}, {v: 22869}, {v: 0}, {v: 0}]} , {c:[{v: '08 May 12'}, {v: 130529}, {v: 22975}, {v: 0}, {v: 0}]} , {c:[{v: '09 May 12'}, {v: 130923}, {v: 23095}, {v: 0}, {v: 0}]} , {c:[{v: '10 May 12'}, {v: 131320}, {v: 23224}, {v: 0}, {v: 0}]} , {c:[{v: '11 May 12'}, {v: 131716}, {v: 23334}, {v: 0}, {v: 0}]} , {c:[{v: '12 May 12'}, {v: 132081}, {v: 23480}, {v: 0}, {v: 0}]} , {c:[{v: '13 May 12'}, {v: 132476}, {v: 23620}, {v: 0}, {v: 0}]} , {c:[{v: '14 May 12'}, {v: 132881}, {v: 23736}, {v: 0}, {v: 0}]} , {c:[{v: '15 May 12'}, {v: 133263}, {v: 23842}, {v: 0}, {v: 0}]} , {c:[{v: '16 May 12'}, {v: 133655}, {v: 23974}, {v: 0}, {v: 0}]} , {c:[{v: '17 May 12'}, {v: 134084}, {v: 24080}, {v: 0}, {v: 0}]} , {c:[{v: '18 May 12'}, {v: 134468}, {v: 24226}, {v: 0}, {v: 0}]} , {c:[{v: '19 May 12'}, {v: 134833}, {v: 24396}, {v: 0}, {v: 0}]} , {c:[{v: '20 May 12'}, {v: 135278}, {v: 24531}, {v: 0}, {v: 0}]} , {c:[{v: '21 May 12'}, {v: 135733}, {v: 24640}, {v: 0}, {v: 0}]} , {c:[{v: '22 May 12'}, {v: 136172}, {v: 24767}, {v: 0}, {v: 0}]} , {c:[{v: '23 May 12'}, {v: 136552}, {v: 24879}, {v: 0}, {v: 0}]} , {c:[{v: '24 May 12'}, {v: 136909}, {v: 25005}, {v: 0}, {v: 0}]} , {c:[{v: '25 May 12'}, {v: 137268}, {v: 25117}, {v: 0}, {v: 0}]} , {c:[{v: '26 May 12'}, {v: 137643}, {v: 25238}, {v: 0}, {v: 0}]} , {c:[{v: '27 May 12'}, {v: 138062}, {v: 25357}, {v: 0}, {v: 0}]} , {c:[{v: '28 May 12'}, {v: 138538}, {v: 25476}, {v: 0}, {v: 0}]} , {c:[{v: '29 May 12'}, {v: 139072}, {v: 25638}, {v: 0}, {v: 0}]} , {c:[{v: '30 May 12'}, {v: 139592}, {v: 25800}, {v: 0}, {v: 0}]} , {c:[{v: '31 May 12'}, {v: 140158}, {v: 25936}, {v: 0}, {v: 0}]} , {c:[{v: '01 Jun 12'}, {v: 140682}, {v: 26075}, {v: 0}, {v: 0}]} , {c:[{v: '02 Jun 12'}, {v: 141270}, {v: 26187}, {v: 0}, {v: 0}]} , {c:[{v: '03 Jun 12'}, {v: 141856}, {v: 26309}, {v: 0}, {v: 0}]} , {c:[{v: '04 Jun 12'}, {v: 142381}, {v: 26427}, {v: 0}, {v: 0}]} , {c:[{v: '05 Jun 12'}, {v: 142874}, {v: 26540}, {v: 0}, {v: 0}]} , {c:[{v: '06 Jun 12'}, {v: 143279}, {v: 26637}, {v: 0}, {v: 0}]} , {c:[{v: '07 Jun 12'}, {v: 143785}, {v: 26756}, {v: 0}, {v: 0}]} , {c:[{v: '08 Jun 12'}, {v: 144308}, {v: 26879}, {v: 0}, {v: 0}]} , {c:[{v: '09 Jun 12'}, {v: 144819}, {v: 26989}, {v: 0}, {v: 0}]} , {c:[{v: '10 Jun 12'}, {v: 145375}, {v: 27098}, {v: 0}, {v: 0}]} , {c:[{v: '11 Jun 12'}, {v: 145889}, {v: 27214}, {v: 0}, {v: 0}]} , {c:[{v: '12 Jun 12'}, {v: 146336}, {v: 27326}, {v: 0}, {v: 0}]} , {c:[{v: '13 Jun 12'}, {v: 146810}, {v: 27484}, {v: 0}, {v: 0}]} , {c:[{v: '14 Jun 12'}, {v: 147303}, {v: 27626}, {v: 0}, {v: 0}]} , {c:[{v: '15 Jun 12'}, {v: 147794}, {v: 27735}, {v: 0}, {v: 0}]} , {c:[{v: '16 Jun 12'}, {v: 148245}, {v: 27851}, {v: 0}, {v: 0}]} , {c:[{v: '17 Jun 12'}, {v: 148713}, {v: 28001}, {v: 0}, {v: 0}]} , {c:[{v: '18 Jun 12'}, {v: 149242}, {v: 28127}, {v: 0}, {v: 0}]} , {c:[{v: '19 Jun 12'}, {v: 149749}, {v: 28252}, {v: 0}, {v: 0}]} , {c:[{v: '20 Jun 12'}, {v: 150246}, {v: 28373}, {v: 0}, {v: 0}]} , {c:[{v: '21 Jun 12'}, {v: 150717}, {v: 28482}, {v: 0}, {v: 0}]} , {c:[{v: '22 Jun 12'}, {v: 151152}, {v: 28604}, {v: 0}, {v: 0}]} , {c:[{v: '23 Jun 12'}, {v: 151614}, {v: 28735}, {v: 0}, {v: 0}]} , {c:[{v: '24 Jun 12'}, {v: 152147}, {v: 28854}, {v: 0}, {v: 0}]} , {c:[{v: '25 Jun 12'}, {v: 152726}, {v: 28969}, {v: 0}, {v: 0}]} , {c:[{v: '26 Jun 12'}, {v: 153214}, {v: 29101}, {v: 0}, {v: 0}]} , {c:[{v: '27 Jun 12'}, {v: 153674}, {v: 29247}, {v: 0}, {v: 0}]} , {c:[{v: '28 Jun 12'}, {v: 154085}, {v: 29372}, {v: 0}, {v: 0}]} , {c:[{v: '29 Jun 12'}, {v: 154481}, {v: 29520}, {v: 0}, {v: 0}]} , {c:[{v: '30 Jun 12'}, {v: 154886}, {v: 29675}, {v: 0}, {v: 0}]} , {c:[{v: '01 Jul 12'}, {v: 155391}, {v: 29828}, {v: 0}, {v: 0}]} , {c:[{v: '02 Jul 12'}, {v: 155971}, {v: 29967}, {v: 0}, {v: 0}]} , {c:[{v: '03 Jul 12'}, {v: 156212}, {v: 30005}, {v: 0}, {v: 0}]} , {c:[{v: '07 Jul 12'}, {v: 156213}, {v: 30011}, {v: 0}, {v: 0}]} , {c:[{v: '05 Aug 12'}, {v: 156214}, {v: 30049}, {v: 0}, {v: 0}]} , {c:[{v: '06 Aug 12'}, {v: 156215}, {v: 30052}, {v: 0}, {v: 0}]} , {c:[{v: '10 Aug 12'}, {v: 156216}, {v: 30053}, {v: 0}, {v: 0}]} , {c:[{v: '15 Aug 12'}, {v: 156217}, {v: 30056}, {v: 0}, {v: 0}]} , {c:[{v: '17 Aug 12'}, {v: 156218}, {v: 30060}, {v: 0}, {v: 0}]} ] } ";
        //$endrow=209;
        //$maxrows=210;
    ?>    
        <span style="position:relative;left:0px;"><?=$title?></span>
        <div id="chart_div"></div>
        <div style="margin:0px auto">
        <input class="chart_button" type="button" value="|<" onclick="gstart(1200)" title="start" />
        <input class="chart_button" type="button" value="<<" onclick="prev(2,1200)" title="back" />
        <input class="chart_button" type="button" value="<" onclick="prev(0.9,800)" title="back" />&nbsp;&nbsp;
        <input class="chart_button" type="button" value="▽" onclick="zoomout()" title="zoom out" />
        <input class="chart_button" type="button" value="△" onclick="zoomin()" title="zoom in" />&nbsp;&nbsp;
        <input class="chart_button" type="button" value=">" onclick="next(0.9,800)" title="forward" />
        <input class="chart_button" type="button" value=">>" onclick="next(2,1200)" title="forward" />
        <input class="chart_button" type="button" value=">|" onclick="gend(1200)" title="end" />
        </div>
        <script type="text/javascript">
            var startrow = <?=$startrow?>;
            var endrow = <?=$endrow?>;
            //var maxrows = <?=$maxrows?>;
            var chartdata = <?=$data?>; 
            Start_Google();
        </script>
<?
    } else { ?>
        <p>No site data found</p>
<?  }  ?>
    </div>
    <br/> 
<?
    if (check_perms('site_stats_advanced')) {
        if (isset($_POST[year1])){
            $start = array ($_POST[year1],$_POST[month1],$_POST[day1]);
            $end = array ($_POST[year2],$_POST[month2],$_POST[day2]);
        } else {
            //$start = array (2011,02,01);
            $start =  date('Y-m-d', time() - (3600*24*365));
            $start = explode('-', $start);
            $end =  date('Y-m-d');
            $end = explode('-', $end);
        }
?>      
        <br/>
        <div class="head"></div>
        <div class="box pad center">
            <form method="post" action="">
                <input type="text" style="width:30px" title="day" name="day1" value="<?=$start[2]?>" />
                <input type="text" style="width:30px" title="month" name="month1"  value="<?=$start[1]?>" />
                <input type="text" style="width:50px" title="year" name="year1"  value="<?=$start[0]?>" />
                &nbsp;&nbsp;To&nbsp;&nbsp;
                <input type="text" style="width:30px" title="day" name="day2"  value="<?=$end[2]?>" />
                <input type="text" style="width:30px" title="month" name="month2"  value="<?=$end[1]?>" />
                <input type="text" style="width:50px" title="year" name="year2"  value="<?=$end[0]?>" />
                &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="submit" name="view" value="View stats" />
            </form>
        </div>
<?  }  ?>
        
    
<?
    if (check_perms('site_debug')) { 
?>      
        <br/>
        <div class="head">debug info</div>
        <div id="debuginfo" class="box pad center">
            <form method="post" action="">
                <input type="submit" name="builddata" value="Create old torrent and user data - CAUTION - deletes data before end date" title="basically this a button marked 'Dont press!' ... please resist temptation" />
                &nbsp;&nbsp;&nbsp;End Date:&nbsp;
                <input type="text" style="width:30px" title="day" name="day"  value="01" />
                <input type="text" style="width:30px" title="month" name="month"  value="08" />
                <input type="text" style="width:50px" title="year" name="year"  value="2012" />
            </form>    
            <span style="float:left">
                <a href="#debuginfo" onclick="$('#databox').toggle(); this.innerHTML=(this.innerHTML=='(Hide chart data)'?'(View chart data)':'(Hide chart data)'); return false;">(View chart data)</a>
            </span>&nbsp;
            
            <div id="databox" class="box pad hidden">
            <?=$data?>
            </div>
        </div>
<?  }  ?>
</div>
<?
show_footer();
