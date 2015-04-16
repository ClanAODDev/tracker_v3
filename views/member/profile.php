<div class='container'>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='divisions/<?php echo $divisionInfo->short_name; ?>'><?php echo $divisionInfo->full_name; ?></a></li>
		<?php echo $platoonInfo->link ?>
		<li class='active'><?php echo $memberInfo->forum_name; ?></li>
	</ul>


	<div class='page-header vertical-align'>
		<div class='col-xs-1 hidden-sm hidden-xs'><?php echo Member::avatar($memberInfo->member_id, 'large'); ?></div>

		<div class='col-xs-5'>
			<h2><strong><?php echo $memberInfo->rank . " " . $memberInfo->forum_name; ?></strong><br /><a class='btn btn-default btn-xs popup-link' href='<?php echo PRIVMSG . $memberInfo->member_id ?>' target='_blank'><i class='fa fa-comment'></i> Send PM</a><a class='btn btn-default btn-xs popup-link' href='<?php echo EMAIL . $memberInfo->member_id ?>' target='_blank'><i class='fa fa-envelope'></i> Send Email</a></h2>
		</div>			

		<div class='col-xs-6'>
			<?php if (User::canEdit($memberInfo->member_id, $user, $member)) : ?>
				<div class='btn-group pull-right' data-member-id='<?php echo $memberInfo->member_id ?>'>
					<button type='button' class='btn btn-info edit-member'><i class="fa fa-pencil fa-lg"></i> <span class="hidden-sm hidden-xs">Edit Profile</span></button>
					<button type='button' class='btn btn-success'><i class="fa fa-user-plus fa-lg"></i> <span class="hidden-sm hidden-xs">Promote</span></button>
					<?php if ($user->role >= 2 && $member->rank_id >= 9 && $memberInfo->status_id != 4) : ?>
						<a href="<?php echo REMOVE . $player->member_id ?>" title="Remove player from AOD" class='removeMember btn btn-danger'><i class='fa fa-trash fa-lg'></i> <span class="hidden-sm hidden-xs">Remove from AOD</span></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class='row margin-top-20'>
		<div class='col-md-3'>

			<?php echo $member_data ?>

		</div>
		<!--/end left side bar-->

		<div class='col-md-9'>
			
			<?php echo $alerts ?>

			<div class='panel panel-info'>
				<div class='panel-heading'><strong>AOD Participation</strong><span class='badge pull-right'><?php echo $aodGames ?> Games</span></div>
				<div class='panel-body'>
					<div class='progress text-center follow-tool' title='<small><center><?php echo $aodGames ?> of <?php echo $totalGames ?><br /><?php echo $pctAod ?>%</center></small>' style='width: 100%; margin: 0 auto; height: 30px; vertical-align:middle;'>
						<div class='progress-bar progress-bar-<?php echo getPercentageColor($pctAod); ?> progress-bar-striped active' role='progressbar' aria-valuenow='72' aria-valuemin='0' aria-valuemax='50' style='width: <?php echo $pctAod ?>%'>
							<span style='display: none;'><?php echo $pctAod; ?>%</span>
						</div>
					</div>
				</div>
			</div>

			<div class='panel panel-primary'>

				<div class='panel-heading'><strong>BF Server Activity</strong> (<?php echo $totalGames ?> games in 30 days)<span class='pull-right'> Last <?php echo MAX_GAMES_ON_PROFILE ?> games</span></div>
				<?php if (count($games)) : ?>

					<?php foreach ($games as $game) : ?>
						<li class="list-group-item clearfix">
							<span class="pull-right">
								<?php if (isset($game->map_name)) : ?><img src='assets/images/maps/<?php echo strtolower($game->map_name); ?>.jpg' title="<?php echo $game->map_name ?>" style="width: 90px;"/>
								<?php endif; ?>
							</span>

							

							<span class="pull-left" style="margin-right: 20px;"><img src="assets/images/game_icons/medium/<?php echo $game->game_id ?>.png"/></span>
							<span class="pull-left">
								<a href="<?php echo generate_report_link($game->game_id, $game->report_id); ?>" target="_blank"><?php echo $game->server ?></a><br /><span class="text-muted">Played <?php echo formatTime(strtotime($game->datetime)); ?></span>
							</span>
						</li>


					<?php endforeach; ?>


				<?php else: ?>
					<li class='list-group-item text-muted'>No information currently available for this player.</li>
				<?php endif; ?>
			</div>

		</div><!-- end right side -->
	</div><!-- end row -->
</div><!-- end container -->