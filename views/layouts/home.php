<div class='container margin-top-20'>

	<!-- alerts and important notifications -->
	<div class='row'>
		<div class='col-md-12'>
			<?php echo $notifications_list ?>
		</div>
	</div>

	<?php if ($user->role == 0): ?>
		<div class="row">
			<div class="container">
				<div class="jumbotron">
					<h1>Hello, <strong><?php echo ucwords($user->username); ?></strong>!</h1>
					<p>Welcome to the AOD Division Tracker, a tool for managing the members within your division in conjunction with the Angels of Death gaming community.</p><p>As a clan member, you have access to see the activity data for all members within the clan, so long as your particular division is supported by this tool. To get started, select a division!</p>
				</div>
			</div>
		</div>
	<?php else: ?>

		<div class="row">
			<div class="container">
				<div class="jumbotron">
					<h1>Hello, <strong><?php echo ucwords($user->username); ?></strong>!</h1>
					<p>Welcome to the AOD Division Tracker, a tool for managing the members within your division in conjunction with the Angels of Death gaming community.</p>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!-- main division list -->
	<div class='row'>
		<div class='col-md-12'>
			<?php echo $divisions_list ?>
		</div>
	</div>

	<?php if ($user->role > 0) : ?>

		<!-- quick tools and personnel view, posts-->

		<div class='row'>
			<div class='col-md-7'>
				<?php echo $main_tools ?>
				<?php echo $personnel ?>
			</div>
			<div class='col-md-5'>
				<div class="panel panel-info">
					<div class="panel-heading"><strong>Recent Activity</strong></div>
					<ul class="activity-list">
						<?php foreach(UserAction::find_all($division->id,15) as $action) : ?>
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
			</div>
		</div>

	<?php endif; ?>

</div>
