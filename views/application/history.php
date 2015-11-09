<ul class="activity">
	<?php foreach(UserAction::find_all() as $action) : ?>
		<li>
			<i class="<?php echo $action->icon; ?> fa-2x"></i>
			<div>
				<?php echo UserAction::humanize($action->type_id, $action->target_id, $action->user_id, $action->verbage); ?>
				<span><?php echo formatTime(strtotime($action->date)); ?></span>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
