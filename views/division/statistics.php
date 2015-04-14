<div class='row col-md-12 margin-top-50'>

	<div class='page-header'>
		<h3><i class="fa fa-tachometer fa-lg"></i> Division Statistics</h3>
	</div>
</div>

<div class='row'>

	<div class='col-md-6'>
		<div class='panel panel-primary toplist'>

			<div class='panel-heading'>Daily Most Active Players<span class='pull-right'>Games</span></div>
			<table class='table table-striped table-hover'>
				<table class='table table-striped table-hover'>
					<?php $i = 1; ?>
					<?php foreach($daily as $player) : ?>
						<tr data-id='<?php echo $player->member_id ?>'><td class='text-center text-muted'><small><?php echo $i; $i++ ?></small></td><td><?php echo $player->forum_name;?></td><td class='text-center text-muted'><?php echo ordSuffix($player->plt); ?> Platoon</td><td><strong><?php echo $player->aod_games ?></strong></td></tr>
					<?php endforeach; ?>

				</table>
			</table>
		</div>
	</div>

	<div class='col-md-6'>
		<div class='panel panel-primary toplist'>

			<div class='panel-heading'>Monthly Most Active Players<span class='pull-right'>Games</span></div>
			<table class='table table-striped table-hover'>
				<?php $i = 1; ?>
				<?php foreach($monthly as $player) : ?>
					<tr data-id='<?php echo $player->member_id ?>'><td class='text-center text-muted'><small><?php echo $i; $i++ ?></small></td><td><?php echo $player->forum_name;?></td><td class='text-center text-muted'><?php echo ordSuffix($player->plt); ?> Platoon</td><td><strong><?php echo $player->aod_games ?></strong></td></tr>
				<?php endforeach; ?>

			</table>
		</div>
	</div>
</div>

