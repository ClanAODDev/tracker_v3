<?php if ($user->role == 1) : ?><!-- if squad leader -->

	<?php $squad_id = (Squad::mySquadId($member->id)) ? Squad::mySquadId($member->id) : NULL; ?>
	<?php $squadMembers = arrayToObject(Squad::findSquadMembers($squad_id)); ?>

	<?php if (!is_null($squad_id)) : ?>
		<?php if (count((array)$squadMembers)) : ?>

			<div class='panel panel-default'>
				<div class='panel-heading'><strong> Your Squad</strong> <span class="pull-right"><?php echo count((array) $squadMembers); ?> members</span></div>
				<div class='list-group' id='squad'>

					<?php foreach($squadMembers as $player) : ?>
						<?php $rctFlag = ($player->recruiter == $member->member_id) ? "<sup><i class='fa fa-asterisk text-success'></i></sup>" : NULL; ?>
						<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo Rank::convert($player->rank_id)->abbr; ?> <?php echo $player->forum_name ?></span> <?php echo $rctFlag ?>
							<?php if (Member::isOnLeave($player->member_id)) : ?>
								<small class='pull-right text-muted'>On Leave</small>
							<?php else: ?>
								<small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small>
							<?php endif; ?></a>
						<?php endforeach; ?>		


					</div>

					<div class='panel-footer'>
						<small class="text-muted margin-top-10"><i class='fa fa-asterisk text-success'></i> - Direct recruit</small><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div>
					</div>
				</div>

			<?php endif; ?>
		<?php endif; ?>

	<?php elseif ($user->role == 2) : ?><!-- if platoon leader -->

		<div class='panel panel-default'>
			<div class='panel-heading'><strong> Your Platoon</strong> <span class=" pull-right"><?php echo Platoon::countPlatoon($member->platoon_id); ?> members</span></div>
			<div class='list-group' id='squads'>
				<?php if (count((array) $squads)) : $i = 1;	?>

					<!-- get squads -->

					<?php foreach($squads as $squad) : ?>

						<?php $leader = Member::findById($squad->leader_id); ?>

						<a href='#collapseSquad_<?php echo $i; ?>' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>
							<?php if ($squad->leader_id != 0): ?>

								<?php echo ordsuffix($i) . " Squad - " . Rank::convert($leader->rank_id)->abbr ?> <?php echo $leader->forum_name ?> <span class="badge"><?php echo Squad::countSquadMembers($squad->id); ?></span>

							<?php else: ?>

								<?php echo ordsuffix($i) ?> Squad <span class="badge"><?php echo Squad::countSquadMembers($squad->id); ?></span>

							<?php endif; ?>
						</a>

						<!-- get squad members -->
						<div class='squad-group collapse' id='collapseSquad_<?php echo $i; ?>'>

							<?php $squadMembers = arrayToObject(Squad::findSquadMembers($squad->id)); ?>

							<?php foreach($squadMembers as $player) : ?>
								<?php $rctFlag = ($player->recruiter == $leader->member_id) ? "<sup><i class='fa fa-asterisk text-success'></i></sup>" : NULL; ?>
								<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo Rank::convert($player->rank_id)->abbr ?> <?php echo $player->forum_name ?></span> <?php echo $rctFlag ?>
									<?php if (Member::isOnLeave($player->member_id)) : ?>
										<small class='pull-right text-muted'>On Leave</small>
									<?php else: ?>
										<small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small>
									<?php endif; ?></a>
								<?php endforeach; ?>
							</div>

							<?php $i++; ?>
						<?php endforeach;  ?>

					<?php endif; ?>
				</div>
				<div class='panel-footer'><small class="text-muted margin-top-10"><i class='fa fa-asterisk text-success'></i> - Direct recruit</small><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div>

			</div>
		</div>

	<?php endif; ?>