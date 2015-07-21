<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
	</div>
</div>

<div class='panel panel-primary'>
	<div class='panel-heading'>Forum Activity</div>
	<div class='panel-body striped-bg'>
		<div id="canvas-holder">
			<canvas id="chart-area" style="filter: drop-shadow(0px 0px 10px rgba(0,0,0,.5));"/>
		</div>
	</div>
</div>


<script type="text/javascript">

	var donutData = <?php echo $activity; ?>;

	window.onload = function() {
		var ctx = document.getElementById("chart-area").getContext("2d");
		window.myDonut = new Chart(ctx).Doughnut(donutData, {
			responsive: true,
			animationEasing: "easeInOutQuint",
			animationSteps : 75,
			percentageInnerCutout : 30,
			segmentShowStroke : true,
			segmentStrokeWidth : 2,
		});
	};

</script>

