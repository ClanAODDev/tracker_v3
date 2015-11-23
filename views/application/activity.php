<div class="panel panel-info">
	<div class="panel-heading"><strong>Recent Activity</strong></div>
	<ul class="activity-list">
	<?php $actions = UserAction::find_all($division->id, 15); ?>
	<?php var_dump(Flight::aod()->last_query); ?>
		<?php foreach($actions as $action) : ?>
			<?php if ( ! is_null ( $action->target_id ) ): ?>
				<li>
					<i class="<?php echo $action->icon; ?> fa-2x"></i>
					<div>
						<?php echo UserAction::humanize($action->type_id, $action->target_id, $action->user_id, $action->verbage); ?>
						<span><?php echo formatTime(strtotime($action->date)); ?></span>
					</div>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>

	</ul>
</div>
