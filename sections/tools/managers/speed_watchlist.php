<?

include(SERVER_ROOT . '/sections/tools/managers/speed_functions.php');

 
show_header('Watchlist','watchlist');

?>
<div class="thin">
    <h2>Speed Watchlist</h2>
     
	<div class="linkbox"> 
       
		<a href="tools.php?action=speed_watchlist&view=issued">[Watchlist]</a>
		<a href="tools.php?action=speed_cheats">[Speed Cheats]</a>
		<a href="tools.php?action=speed_records">[Speed Records]</a>
	</div>
    <br/>
<?
    $Watchlist = print_user_watchlist();
     
    $Excludelist = print_user_notcheatslist();
?>
     
</div>
<? show_footer(); ?>