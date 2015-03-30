<!-- 	if ($game_id == 2) {

// statistics
$toplistMonthly = null;
$monthly = get_monthly_bf4_toplist(10);
$i = 1;
foreach ($monthly['players'] as $mem) {
$toplistMonthly .= "<tr data-id='{$mem['member_id']}'><td class='text-center text-muted'>{$i}</td><td>{$mem['rank']} {$mem['forum_name']}</td><td><strong>{$mem['aod_games']}</strong></td></tr>";
$i++;
}

$toplistDaily = null;
$daily = get_daily_bf4_toplist(10);

$i = 1;
foreach ($daily as $mem) {
$toplistDaily .= "<tr data-id='{$mem['member_id']}'><td class='text-center text-muted'>{$i}</td><td>{$mem['rank']} {$mem['forum_name']}</td><td><strong>{$mem['aod_games']}</strong></td></tr>";
$i++;
}

// end statistics

}
// bf statistics
if ($game_id == 2) {
-->
 <!-- 
 <div class='row col-md-12 margin-top-50'>
 
 	<div class='page-header'>
 		<h3>Division Statistics</h3>
 	</div>
 </div>
 
 <div class='row'>
 
 	<div class='col-md-6'>
 		<div class='panel panel-primary toplist'>
 
 			<div class='panel-heading'>Daily Most Active Players</div>
 			<table class='table table-striped table-hover'>
 				{$toplistDaily}
 			</table>
 		</div>
 	</div>
 
 	<div class='col-md-6'>
 		<div class='panel panel-primary toplist'>
 
 			<div class='panel-heading'>Monthly Most Active Players</div>
 			<table class='table table-striped table-hover'>
 				{$toplistMonthly}
 			</table>
 		</div>
 	</div>
 </div>
-->
