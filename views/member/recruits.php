<?php if (count($recruits)) : $rctCount = count($recruits); ?>
	<?php $recruits = arrayToObject($recruits); ?>
	<div class='panel panel-primary'>
		<div class='panel-heading'><strong><?php echo $member->forum_name ?>'s Recruiting History</strong> <span class="pull-right"><?php echo $rctCount ?> members</span></div>
		<div class='list-group'>
			<?php foreach ($recruits as $player) : ?>
				<a href="member/<?php echo $player->member_id ?>" class="list-group-item clearfix">
					<span class="col-xs-5"><?php echo $player->abbr . " " . $player->forum_name ?></span>
					<span class="col-xs-5 text-muted">Recruited <?php echo formatTime(strtotime($player->join_date)); ?></span>
					<?php if ($player->status_id == 4) : ?>
						<span class="col-xs-2 text-center text-danger"><i class="fa fa-times"></i> Removed</span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
<?php else : ?>
	<span class="list-group-item">Either this player has no recruits, or there are no associations to this player on any existing member's record.</span>
<?php endif; ?>