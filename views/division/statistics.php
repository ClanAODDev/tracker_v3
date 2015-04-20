<div class='row col-md-12 margin-top-50'>

	<div class='page-header'>
		<h3><i class="fa fa-tachometer fa-lg"></i> Division Statistics</h3>
	</div>
</div>

<div class='row'>

	<div class='col-md-4'>
		<div class='panel panel-primary toplist'>

			<div class='panel-heading'><i class="fa fa-bolt"></i> Daily Most Active Players<span class='pull-right'>Games</span></div>
			<table class='table table-striped table-hover'>
				<table class='table table-striped table-hover'>
					<?php $i = 1; ?>
					<?php foreach($daily as $player) : ?>
						<tr data-id='<?php echo $player->member_id ?>'><td class='text-center text-muted'><small><?php echo $i; $i++ ?></small></td><td><?php echo $player->rank ?> <?php echo $player->forum_name;?></td><td class='text-center text-muted'><?php echo ordSuffix($player->plt); ?> Platoon</td><td><strong><?php echo $player->aod_games ?></strong></td></tr>
					<?php endforeach; ?>

				</table>
			</table>
		</div>
	</div>

	<div class='col-md-4'>
		<div class='panel panel-primary toplist'>

			<div class='panel-heading'><i class="fa fa-calendar"></i> Monthly Most Active Players<span class='pull-right'>Games</span></div>
			<table class='table table-striped table-hover'>
				<?php $i = 1; ?>
				<?php foreach($monthly as $player) : ?>
					<tr data-id='<?php echo $player->member_id ?>'><td class='text-center text-muted'><small><?php echo $i; $i++ ?></small></td><td><?php echo $player->rank ?> <?php echo $player->forum_name;?></td><td class='text-center text-muted'><?php echo ordSuffix($player->plt); ?> Platoon</td><td><strong><?php echo $player->aod_games ?></strong></td></tr>
				<?php endforeach; ?>

			</table>
		</div>
	</div>

	<div class='col-md-4'>
	<div class='panel panel-info toplist'>
			<div class='panel-heading'>Total Membership</div>
			<div class="panel-body count-detail-big follow-tool striped-bg"><span class="count-animated"><?php echo $personnelData->totalCount ?></span>
			</div>
		</div>

		<div class='panel panel-info toplist'>
			<div class='panel-heading'>Recruits This Month</div>
			<div class="panel-body count-detail-big follow-tool striped-bg"><span class="count-animated"><?php echo $personnelData->recruitsThisMonth ?></span>
			</div>
		</div>		
	</div>
</div>

