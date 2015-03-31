<div class='container'>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='divisions/<?php echo $divisionInfo->short_name; ?>'><?php echo $divisionInfo->full_name; ?></a></li>
		<?php echo $platoonInfo->link ?>
		<li class='active'><?php echo $memberInfo->forum_name; ?></li>
	</ul>


	<div class='page-header vertical-align'>
		<div class='col-xs-1 hidden-sm hidden-xs'><?php echo Member::avatar($memberInfo->member_id, 'large'); ?></div>
		<div class='col-xs-7'>
			<h2><strong><?php echo $memberInfo->rank . " " . $memberInfo->forum_name; ?></strong><br /><a class='btn btn-default btn-xs popup-link' href='<?php echo PRIVMSG . $memberInfo->member_id ?>' target='_blank'><i class='fa fa-comment'></i> Send PM</a><a class='btn btn-default btn-xs popup-link' href='<?php echo EMAIL . $memberInfo->member_id ?>' target='_blank'><i class='fa fa-envelope'></i> Send Email</a></h2>
		</div>			
		<div class='col-xs-4'>


			<?php if (User::canEdit($memberInfo->member_id, $user, $member)) : ?>
				<div class='btn-group pull-right' data-member-id='<?php echo $memberInfo->member_id ?>'>
					<button type='button' class='btn btn-default edit-member'>Edit</button>
					<button type='button' class='btn btn-default disabled'>Promote</button>
					<?php if ($user->role == 2 && $member->rank_id == 9) : ?>
						<button type='button' class='btn btn-danger disabled'>Remove From AOD</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php echo $alerts ?>


	<div class='row margin-top-20'>
		<div class='col-md-3'>

			<div class='panel panel-info'>
				<div class='panel-heading'><strong>Member Information</strong></div>
				<ul class='list-group'>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Status: </strong></span> <span class='text-muted'><?php echo $memberInfo->desc ?></span></li>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Division: </strong></span> <span class='text-muted'><?php echo $divisionInfo->full_name ?></span></li>
					<?php echo $platoonInfo->item ?>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Position: </strong></span> <span class='text-muted'><?php echo $memberInfo->position ?></span></li>
					<?php $squadleader = ($memberInfo->squad_leader_id != 0) ? $memberInfo->squad_leader_id : NULL; ?>
					
					<?php if (!is_null($squadleader)) : ?>
						<a href="member/<?php echo $squadleader ?>" class="list-group-item text-right">
							<span class='pull-left'><strong>Squad Leader: </strong></span> 
							<span class='text-muted'><?php echo Member::findForumName($squadleader) ?></a></span>
						</a>
					<?php endif; ?>

					<?php $recruiter = ($memberInfo->recruiter != "0") ? $memberInfo->recruiter : NULL; ?>
					<?php if (!is_null($recruiter) && $recruiter !== $memberInfo->recruiter) : ?>
						<a href="member/<?php echo $recruiter ?>" class="list-group-item text-right">
							<span class='pull-left'><strong>Recruiter: </strong></span> 
							<span class='text-muted'><?php echo Member::findForumName($recruiter) ?></a></span>
						</a>
					<?php endif; ?>
					
				</ul>
			</div>

			<div class='panel panel-info'>
				<div class='panel-heading'><strong>Forum Activity</strong></div>
				<ul class='list-group'>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Joined:</strong></span> <span class='text-muted'><?php echo date('Y-m-d', strtotime($memberInfo->join_date)); ?></span></li>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Last seen:</strong></span> <span class='text-muted'><?php echo formatTime(strtotime($memberInfo->last_activity)); ?></span></li>
					<li class='list-group-item text-right'><span class='pull-left'><strong>Last posted:</strong></span>  <span class='text-muted'><?php echo formatTime(strtotime($memberInfo->last_forum_post)); ?></span></li>
				</ul>
			</div>

			<div class='panel panel-info'>
				<div class='panel-heading'>
					<strong>Gaming Profiles</strong>
				</div>

				<a target='_blank' href='<?php echo CLANAOD . $memberInfo->member_id ?>' class='list-group-item'>AOD Forum <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>

				<?php if ($memberInfo->battlelog_name !== "0") : ?>
					<a target="_blank" href="<?php echo BATTLELOG . $memberInfo->battlelog_name ?>" class="list-group-item">Battlelog <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>
				<?php endif; ?>

				<a target='_blank' href='<?php echo BF4DB . $memberInfo->bf4db_id ?>' class='list-group-item'>BF4DB <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>

			</div>

		</div>
		<!--/end left side bar-->

		<div class='col-md-9'>
			{$loaStatus}

			<div class='panel panel-info'>
				<div class='panel-heading'><strong>AOD Participation</strong><span class='badge pull-right'>{$aod_games} Games</span></div>
				<div class='panel-body'>
					<div class='progress text-center follow-tool' title='<small><center>{$aod_games} of {$count_all_games}<br />{$percent_aod}%</center></small>' style='width: 100%; margin: 0 auto; height: 30px; vertical-align:middle;'>
						<div class='progress-bar progress-bar-" . getPercentageColor($percent_aod) . " progress-bar-striped active' role='progressbar' aria-valuenow='72' aria-valuemin='0' aria-valuemax='50' style='width: ". $percent_aod . "%'>
							<span style='display: none;'>{$percent_aod}%</span>
						</div>
					</div>
				</div>
			</div>

			<div class='panel panel-primary'>

				<div class='panel-heading'><strong>Server Activity</strong> ({$count_all_games} games in 30 days)<span class='pull-right'> Last {$maxGames} games</span></div>
				<?php if (count($games)) : ?>
					<table class='table table-striped table-hover'>
						<tbody>
							<?php foreach ($games as $game) : ?>
								<tr>
									<td><?php echo $game->server ?></td>
									<td class='text-muted'><?php echo formatTime(strtotime($game->datetime)); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<li class='list-group-item text-muted'>Either this player has no recorded games or the data sync has not yet stored any data for this player. It's also possible that this player only plays Battlefield:Hardline, which is not currently yet being synced.</li>
				<?php endif; ?>
			</div>

		</div><!-- end right side -->
	</div><!-- end row -->
</div><!-- end container -->