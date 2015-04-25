<?php if ($member->position_id == 5) : ?><!-- if squad leader -->
	<?php if (Squad::count($member->member_id)) : ?>
		<div class='panel panel-primary'>
			<div class='panel-heading'><strong><?php echo $member->forum_name ?>'s Squad</strong> <span class="pull-right"><?php echo Squad::count($member->member_id); ?> members</span></div>
			<div class='list-group'>
				<?php foreach(Squad::find($member->member_id) as $player) : ?>
					<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo $player->rank ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>
				<?php endforeach; ?>				
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>