<div class='panel panel-info'>
	<div class='panel-heading'><strong>AOD Participation</strong><span class='badge pull-right'><?php echo $aodGames ?> Games</span></div>
	<div class='panel-body'>
		<div class='progress text-center follow-tool' title='<small><center><?php echo $aodGames ?> of <?php echo $totalGames ?><br /><?php echo round($pctAod, 2); ?>%</center></small>' style='width: 100%; margin: 0 auto; height: 30px; vertical-align:middle;'>
			<div class='progress-bar progress-bar-<?php echo getPercentageColor($pctAod); ?> progress-bar-striped active' role='progressbar' aria-valuenow='72' aria-valuemin='0' aria-valuemax='50' style='width: <?php echo $pctAod ?>%'>
				<span style='display: none;'><?php echo $pctAod; ?>%</span>
			</div>
		</div>
	</div>
</div>

<div class='panel panel-primary'>

	<div class='panel-heading'><strong>BF Server Activity</strong> (<?php echo $totalGames ?> games in 30 days)<span class='pull-right'> Last <?php echo MAX_GAMES_ON_PROFILE ?> games</span></div>

	<?php if ($totalGames > 0) : ?>
		<?php $games = arrayToObject($games); ?>
		<?php foreach ($games as $game) : ?>
			<a class="list-group-item clearfix" href="<?php echo generate_report_link($game->game_id, $game->report_id); ?>" target="_blank">
				<span class="pull-right">

					<?php if (!is_null($game->map_name)) : ?>
						<img src='assets/images/maps/<?php echo strtolower($game->map_name); ?>.jpg' class="img-thumbnail" title="<?php echo $game->map_name ?>" style="width: 90px;"/>
					<?php endif; ?>
				</span>
				<?php if ($game->game_id != 2) : ?>
					<span class="pull-left" style="margin-right: 20px;"><img class="img-thumbnail"  src="assets/images/game_icons/medium/<?php echo $game->game_id ?>.png"/></span>

				<?php endif; ?>
				<span class="pull-left">
					<?php echo $game->server ?><br /><span class="text-muted">Played <?php echo formatTime(strtotime($game->datetime)); ?></span>
				</span>
			</a>
		<?php endforeach; ?>



	<?php else: ?>
		<li class='list-group-item text-muted'>No information currently available for this player, or player has not played any games in the past 30 days. Please ensure that game activity is not hidden by the user.</li>
	<?php endif; ?>
</div>
