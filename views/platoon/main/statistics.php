<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
	</div>
</div>

<div class='panel panel-primary'>
	<div class='panel-heading'>Activity</div>
	<div class='panel-body striped-bg'><div id="activity" style="width: 200px; height: 200px; margin: 0 auto;"></div>
</div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Task', 'Hours per Day'],
			['Players', 80],
			['Sold', 5],
			['Open', 15]
			]);

		var options = {
			is3D: true,
			position: 'labeled',
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			colors: ['#83768d', '#b7c777', '#959c9c']
		};

		var chart = new google.visualization.PieChart(document.getElementById('activity'));
		chart.draw(data, options);
	}
</script>

<!-- <div class='panel panel-primary'>
	<div class='panel-heading'>Percentage AOD Games</div>
	<div class='panel-body count-detail-big follow-tool striped-bg' title='<center><strong>AOD Games</strong><br /><?php //echo $gameStats->AOD . " out of " . $gameStats->total; ?></center>'><span class='count-animated percentage'><?php // echo $gameStats->pct; ?>%</span>
	</div>
</div> -->

<!-- show squad if squad leader in platoon being viewed
<div class='panel panel-default'>
	<div class='panel-heading'><strong> Your Squad</strong> {$squadCount}<span class='pull-right text-muted'>Last seen</span></div>

	<div class='list-group' id='squad'>
		{$my_squad}
	</div>
</div> -->