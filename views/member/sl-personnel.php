<?php if ($member->position_id == 5) : ?><!-- if squad leader -->

	<?php $squad_id = Squad::mySquadId($member->id); ?>
	<?php $squadMembers = arrayToObject(Squad::findSquadMembers($squad_id)); ?>

	<?php if (count((array)$squadMembers)) : ?>

		<div class='panel panel-primary'>
			<div class='panel-heading'><strong><?php echo $member->forum_name ?>'s <?php echo Locality::run('Squad', $member->game_id); ?></strong> <span class="pull-right"><?php echo count((array) $squadMembers); ?> members</span></div>
			<div class='list-group'>

				<?php foreach($squadMembers as $player) : ?>

					<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo Rank::convert($player->rank_id)->abbr ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>

				<?php endforeach; ?>	

			</div>
		</div>
	<?php else: ?>
		<li class="list-group-item text-muted">No members assigned</li>
	<?php endif; ?>

<?php endif; ?>

