<?php if (count($recruits)) : ?>
	<?php foreach ($recruits as $player) : ?>
		<a href="member/<?php echo $player->member_id ?>" class="list-group-item clearfix">
			<span class="col-xs-5"><?php echo $player->forum_name ?></span>
			<span class="col-xs-5 text-muted">Recruited <?php echo formatTime(strtotime($player->join_date)); ?></span>
		</a>
	<?php endforeach; ?>
<?php else : ?>
	<span class="list-group-item">Either this player has no recruits, or there are no associations to this player on any existing member's record.</span>
<?php endif; ?>