<?php $pltCount = Platoon::countPlatoon($platoon->id); ?>
<div class='panel panel-primary'>
	<div class='panel-heading'>Total Members</div>
	<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo $pltCount; ?></span>
	</div>
</div>
<?php if (count($pltCount)): ?>
	<div class='panel panel-primary'>
		<div class='panel-heading'>Forum Activity</div>
		<div class='panel-body striped-bg'>
			<div id="canvas-holder" data-stats="<?php echo htmlentities($activity, ENT_QUOTES, 'UTF-8'); ?>">
				<canvas id="chart-area" style="filter: drop-shadow(0px 0px 10px rgba(0,0,0,.5));"/>
			</div>
		</div>
	</div>
<?php endif; ?>