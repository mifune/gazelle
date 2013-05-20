<?
if (!list($Countries,$Rank,$CountryUsers,$CountryMax,$CountryMin,$LogIncrements,$CountryUsersNum,$CountryName) = $Cache->get_value('geodistribution')) {
	include_once(SERVER_ROOT.'/classes/class_charts.php');
	$DB->query('SELECT Code, Users, country FROM users_geodistribution AS ug LEFT JOIN countries AS c ON c.cc=ug.Code ORDER BY Users DESC');
	$Data = $DB->to_array();
	$Count = $DB->record_count()-1;
	
	if($Count<30) {
		$CountryMinThreshold = $Count;
	} else {
		$CountryMinThreshold = 30;
	}
	
	$CountryMax = ceil(log(Max(1,$Data[0][1]))/log(2))+1;
	$CountryMin = floor(log(Max(1,$Data[$CountryMinThreshold][1]))/log(2));

	$CountryRegions = array('RS' => array('RS-KM')); // Count Kosovo as Serbia as it doesn't have a TLD
    $i=0;
	foreach ($Data as $Key => $Item) {
		list($Country,$UserCount,$CName) = $Item;
		$Countries[$i] = $Country;
		$CountryUsers[$i] = number_format((((log($UserCount)/log(2))-$CountryMin)/($CountryMax-$CountryMin))*100,2);
		$Rank[$i] = round((1-($Key/$Count))*100);
        $CountryUsersNum[$i] = $UserCount;
        $CountryName[$i] = $CName;
		if(isset($CountryRegions[$Country])) {
			foreach($CountryRegions[$Country] as $Region) {
                $i++;
				$Countries[$i] = $Region;
				$Rank[$i] = end($Rank);
			}
		}
        $i++;
	}
	reset($Rank);
	
	for ($i=$CountryMin;$i<=$CountryMax;$i++) {
		$LogIncrements[] = human_format(pow(2,$i));
	}
	$Cache->cache_value('geodistribution',array($Countries,$Rank,$CountryUsers,$CountryMax,$CountryMin,$LogIncrements,$CountryUsersNum,$CountryName),0);
}

if(!$ClassDistribution = $Cache->get_value('class_distribution')) {
	include_once(SERVER_ROOT.'/classes/class_charts.php');
	$DB->query("SELECT p.Name, COUNT(m.ID) AS Users FROM users_main AS m JOIN permissions AS p ON m.PermissionID=p.ID WHERE m.Enabled='1' GROUP BY p.Name ORDER BY Users DESC");
	$ClassSizes = $DB->to_array();
	$Pie = new PIE_CHART(750,400,array('Other'=>1,'Percentage'=>1));
	foreach($ClassSizes as $ClassSize) {
		list($Label,$Users) = $ClassSize;
		$Pie->add($Label,$Users);
	}
	$Pie->transparent();
	$Pie->color('FF33CC');
	$Pie->generate();
	$ClassDistribution = $Pie->url();
	$Cache->cache_value('class_distribution',$ClassDistribution,3600*36); // 24*14
}
if(!$PlatformDistribution = $Cache->get_value('platform_distribution')) {
	include_once(SERVER_ROOT.'/classes/class_charts.php');
	
	$DB->query("SELECT OperatingSystem, COUNT(UserID) AS Users FROM users_sessions GROUP BY OperatingSystem ORDER BY Users DESC");
	
	$Platforms = $DB->to_array();
	$Pie = new PIE_CHART(750,400,array('Other'=>1,'Percentage'=>1));
	foreach($Platforms as $Platform) {
		list($Label,$Users) = $Platform;
		$Pie->add($Label,$Users);
	}
	$Pie->transparent();
	$Pie->color('8A00B8');
	$Pie->generate();
	$PlatformDistribution = $Pie->url();
	$Cache->cache_value('platform_distribution',$PlatformDistribution,3600*36);
}

if(!$BrowserDistribution = $Cache->get_value('browser_distribution')) {
	include_once(SERVER_ROOT.'/classes/class_charts.php');
	
	$DB->query("SELECT Browser, COUNT(UserID) AS Users FROM users_sessions GROUP BY Browser ORDER BY Users DESC");
	
	$Browsers = $DB->to_array();
	$Pie = new PIE_CHART(750,400,array('Other'=>1,'Percentage'=>1));
	foreach($Browsers as $Browser) {
		list($Label,$Users) = $Browser;
		$Pie->add($Label,$Users);
	}
	$Pie->transparent();
	$Pie->color('008AB8');
	$Pie->generate();
	$BrowserDistribution = $Pie->url();
	$Cache->cache_value('browser_distribution',$BrowserDistribution,3600*36);
}

// clients we can get from current peers
if(!$ClientDistribution = $Cache->get_value('client_distribution')) {
	include_once(SERVER_ROOT.'/classes/class_charts.php');
	
	$DB->query("SELECT useragent, Count(uid) AS Users FROM xbt_files_users GROUP BY useragent ORDER BY Users DESC");
		
	$Clients = $DB->to_array();
	$Pies[0] = new PIE_CHART(750,400,array('Other'=>0.01,'Percentage'=>1));
	$Pies[1] = new PIE_CHART(750,400,array('Other'=>0.01,'Percentage'=>1));
	$Pies[2] = new PIE_CHART(750,400,array('Other'=>0.01,'Percentage'=>1));
    $Results2=array();
    $Results3=array();
	foreach($Clients as $Client) {
		list($Label,$Users) = $Client;
		$Pies[0]->add($Label,$Users);
                  //  } elseif ($AllowWidth && preg_match('/^([0-9]{1,3})px$/', $att, $matches)) {
        if (preg_match('#^(?|([^/]*)\/([^/]*)\/([^/]*)|([^/]*)\/([^/\(]*)\((.*)\)$#', $Label, $matches)) {
            // matches in form aa/bb/cc or aa/bb(cc) - Label2 == aa/bb
            $Label2 = $matches[1] .'/'.$matches[2];
        } else $Label2 = $Label;
        if (!isset($Results2[$Label2])) $Results2[$Label2] = $Users;
        else $Results2[$Label2] += $Users;
		//$Pies[1]->add($Label2,$Users);
        
        if (preg_match('#^(?|([^/]*)/([^/]*)|([^\s]*)\s([^\s]*)$#', $Label, $matches)) {
            // matches in form aa/bb/cc or aa/bb(cc) - Label2 == aa/bb
            $Label3 = $matches[1] .'/'.$matches[2];
        } else $Label3 = $Label;
        if (!isset($Results3[$Label3])) $Results3[$Label3] = $Users;
        else $Results3[$Label3] += $Users;
		//$Pies[2]->add($Label3,$Users);
	}
	foreach($Results2 as $Label=>$Users) {
		$Pies[1]->add($Label,$Users);
    }
	foreach($Results3 as $Label=>$Users) {
		$Pies[2]->add($Label,$Users);
    }
    $ClientDistribution=array();
	foreach($Pies as $Pie) {
        $Pie->transparent();
        $Pie->color('00D025');
        $Pie->generate();
        $ClientDistribution[] = $Pie->url(); 
    }
	$Cache->cache_value('client_distribution',$ClientDistribution,3600*36);
}

//Timeline generation
if (!list($Labels,$InFlow,$OutFlow,$Max) = $Cache->get_value('users_timeline')) {
	$DB->query("SELECT DATE_FORMAT(JoinDate,'%b \\'%y') AS Month, COUNT(UserID) FROM users_info GROUP BY Month ORDER BY JoinDate DESC LIMIT 1, 12");
	$TimelineIn = array_reverse($DB->to_array());
	$DB->query("SELECT DATE_FORMAT(BanDate,'%b \\'%y') AS Month, COUNT(UserID) FROM users_info GROUP BY Month ORDER BY BanDate DESC LIMIT 1, 12");
	$TimelineOut = array_reverse($DB->to_array());
	foreach($TimelineIn as $Month) {
		list($Label,$Amount) = $Month;
		if ($Amount > $Max) {
			$Max = $Amount;
		}
	}
	foreach($TimelineOut as $Month) {
		list($Label,$Amount) = $Month;
		if ($Amount > $Max) {
			$Max = $Amount;
		}
	}

	$Labels = array();
	foreach($TimelineIn as $Month) {
		list($Label,$Amount) = $Month;
		$Labels[] = $Label;
		$InFlow[] = number_format(($Amount/$Max)*100,4);
	}
	foreach($TimelineOut as $Month) {
		list($Label,$Amount) = $Month;
		$OutFlow[] = number_format(($Amount/$Max)*100,4);
	}
	$Cache->cache_value('users_timeline',array($Labels,$InFlow,$OutFlow,$Max),mktime(0,0,0,date('n')+1,2)); //Tested: fine for dec -> jan
}
//End timeline generation
 
show_header('Detailed User Statistics');

?>
<div class="thin">
    <div class="head">User Flow</div>
    <div class="box pad center">
          <img src="http://chart.apis.google.com/chart?cht=lc&chs=880x160&chco=000D99,99000D&chg=0,-1,1,1&chxt=y,x&chxs=0,h&chxl=1:|<?=implode('|',$Labels)?>&chxr=0,0,<?=$Max?>&chd=t:<?=implode(',',$InFlow)?>|<?=implode(',',$OutFlow)?>&chls=2,4,0&chdl=New+Registrations|Disabled+Users&amp;chf=bg,s,FFFFFF00" />
    </div>
    <br />
    <div class="head">User Classes</div>
    <div class="box pad center">
          <img src="<?=$ClassDistribution?>" />
    </div>
    <br />
    <div class="head">User Platforms</div>
    <div class="box pad center">
          <img src="<?=$PlatformDistribution?>" />
    </div>
    <br />
    <div class="head">User Browsers</div>
    <div class="box pad center">
          <img src="<?=$BrowserDistribution?>" />
    </div>
    <br />
    <div class="head">User Clients</div>
    <div class="box pad center">
        <div class=" ">
        [<a onclick="$('#clientdist1').hide(); $('#clientdist2').hide(); $('#clientdist3').show(); return false;" href="#" >client overview</a>]
        [<a onclick="$('#clientdist1').hide(); $('#clientdist3').hide(); $('#clientdist2').show(); return false;" href="#" >client detail</a>]
         
        [<a onclick="$('#clientdist2').hide(); $('#clientdist3').hide(); $('#clientdist1').show(); return false;" href="#" >client detail+</a>]
         
        </div>
         
        <br />
        <img id="clientdist1" src="<?=$ClientDistribution[0]?>" class="hidden" />
        <img id="clientdist2" src="<?=$ClientDistribution[1]?>" class="hidden" />
        <img id="clientdist3" src="<?=$ClientDistribution[2]?>" />
    </div>
    <br />
    <div class="head">Geographical Distribution Map</div>
    <div class="box center"> 
          <br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=-55,-180,73,180&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=5,-180,70,9&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=37,-16,65,77&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=-56,-132,14,32&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=-36,-57,37,100&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=13,62,60,180&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=-50,60,15,180&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          
          <img src="http://chart.apis.google.com/chart?cht=map:fixed=14.8,15,45,86&chs=720x360&chd=t:<?=implode(',',$Rank)?>&chco=FFFFFF,EDEDED,1F0066&chld=<?=implode('|',$Countries)?>&chf=bg,s,CCD6FF" />
          <br /><br />
          
          <img src="http://chart.apis.google.com/chart?chxt=y,x&chg=0,-1,1,1&chxs=0,h&cht=bvs&chco=76A4FB&chs=880x300&chd=t:<?=implode(',',array_slice($CountryUsers,0,31))?>&chxl=1:|<?=implode('|',array_slice($Countries,0,31))?>|0:|<?=implode('|',$LogIncrements)?>&amp;chf=bg,s,FFFFFF00" />
          <br /><br />
          <table style="width:90%;margin: 0px auto;"> 
<?
    $len = count($Countries);
    $numrows = ceil($len/6);
	for ($i=0;$i<$numrows;$i++) { 
?>
              <tr> 
<?
        for ($k=0;$k<6;$k++) {
            $index = $i+($k*$numrows);
            if ($index >= $len) break;
            if ($index == $len-1 && $k<5) $colspan = ' colspan="'.(6-$k).'"';
            else $colspan='';
?>
                  <td<?=$colspan?> style="width:100px; padding: 0px 10px;">
                      <table style="width:100px; border:1px solid #c4c4c4;<?if ($i<$numrows-1 || $index == $len-1) echo 'border-bottom: none';?>">
                          <tr>
                              <td class="rowa" style="width:50px" title="<?=$CountryName[$index]?>"><?=$Countries[$index]?></td>
                              <td class="rowb" style="width:50px"><?=$CountryUsersNum[$index]?></td>
                          </tr>
                      </table>
                  </td>
<? 
        }
?> 
              </tr>
<?
	}
?>
          </table>
          <br /><br />
          <p class="small">GeoLite data used under Creative Commons Attribution-ShareAlike 3.0 Unported License<br/>GeoLite data from MaxMind, available from http://www.maxmind.com</p>
    </div>
</div>
<?
show_footer();
