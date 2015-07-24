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

	<?php if ($user->role == 0) : ?>

		<!-- posts visible to users / non-leadership -->
		<?php echo $posts_list ?>

	<?php else : ?>

		<!-- quick tools and personnel view, posts-->

		<div class='row'>
			<div class='col-md-5'>
				<?php echo $main_tools ?>
				<?php echo $personnel ?>
			</div>
			<div class='col-md-7'>
				<?php echo $posts_list ?>
			</div>
		</div>

	<?php endif; ?>

</div>
