<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
	</div>
</div>

<div class='panel panel-primary'>
	<div class='panel-heading'>Forum Activity</div>
	<div class='panel-body striped-bg'>
		<div id="activity" style="width: 300px; height: 300px; margin: 0 auto;"></div>
	</div>
</div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Players', 'Forum Activity'],
			['Less than two weeks', <?php echo $activity->underTwoWeeks ?>],
			['More than 2 weeks', <?php echo $activity->twoWeeksMonth ?>],
			['Inactive', <?php echo $activity->oneMonth ?>]
			]);

		var options = {
			is3D: true,
			position: 'labeled',
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			colors: ['#28b62c', '#ff851b', '#ff4136'],
			width:300,
			height:300
		};

		var chart = new google.visualization.PieChart(document.getElementById('activity'));
		chart.draw(data, options);
	}
</script>

