<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
	</div>
</div>

<div class='panel panel-primary'>
	<div class='panel-heading'>Forum Activity</div>
	<div class='panel-body striped-bg'>
		<div id="activity" style="width: 330px; height: 330px; margin: 0 auto; margin-top: -40px; -webkit-filter: drop-shadow(0px 0px 10px rgba(0,0,0,.5));"></div>
	</div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Players', 'Forum Activity'],
			['<2 wks', <?php echo $activity->underTwoWeeks ?>],
			['>2 wks', <?php echo $activity->twoWeeksMonth ?>],
			['>30 days', <?php echo $activity->oneMonth ?>]
			]);

		var options = {
			is3D: true,
			position: 'labeled',
			legend: {position: 'bottom'},
			backgroundColor: { fill:'transparent' },
			colors: ['#28b62c', '#ff851b', '#ff4136'],
			sliceVisibilityThreshold: 0,
			width:330,
			height:330,
			pieStartAngle: 60
		};

		var chart = new google.visualization.PieChart(document.getElementById('activity'));
		chart.draw(data, options);
	}
</script>

