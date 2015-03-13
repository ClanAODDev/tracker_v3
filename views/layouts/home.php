<div class='container fade-in margin-top-20'>
	<!-- welcome area and help button -->
	<div class='row tour-intro'>
		<div class='col-md-12'>
			<div class='jumbotron striped-bg'>
				<h1>Hello, <strong><?php echo $member->forum_name ?></strong>!</h1>
				<p>This is the activity tracker for the <?php echo $division->full_name ?> division! Visit the help section for more information.</p>
				<p><a class='btn btn-primary btn-lg' href='help' role='button'>Learn more <i class='fa fa-arrow-right'></i></a></p>
			</div>
		</div> 
	</div>

	<!-- alerts and important notifications -->
	<div class='row'>
		<div class='col-md-12'>
			<?php echo $notifications_list ?>
		</div>
	</div>

	<!-- main division list -->
	<div class='row'>
		<div class='col-md-12'>
			<?php echo $divisions_list ?>
		</div>
	</div>

	<?php if ($user->role == 0) : ?>

		<!-- posts visible to users / non-leadership -->
		<div class='panel panel-info'>
			<div class='panel-heading'>Welcome to the activity tracker!</div>
			<div class='panel-body'>
				<p>As a clan member, you have access to see the activity data for all members within the clan, so long as your particular division is supported by this tool. To get started, select your division from the \"divisions\" dropdown above.</p>
				<p>To view a particular member, simply type their name in the search box above.</p>
			</div>
		</div>

		<?php echo $posts_list ?>

	<?php else : ?>

		<!-- quick tools and personnel view-->
		<div class='row'>
			<div class='col-md-5'>

				<?php echo $main_tools ?>

				<?php if ($user->role == 1) : ?>

					<div class='panel panel-default'>
						<div class='panel-heading'><strong> Your Squad</strong> {$squadCount}<span class='pull-right text-muted'>Last seen</span></div>

						<div class='list-group' id='squad'>
							{$my_squad}

						</div>
						<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
					</div>

				<?php elseif ($user->role == 2) : ?>

					// platoon

					<div class='panel panel-default'>
						<div class='panel-heading'><strong> Your Platoon</strong> {$platoonCount}<span class='pull-right text-muted'>Last seen</span></div>

						<div class='list-group' id='squads'>

							{$my_platoon}

						</div>
						<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
					</div>

				<?php endif; ?>
			</div>

			<!-- posts visible to leadership -->
			<div class='col-md-7'>
				<?php echo $posts_list ?>
			</div>
		</div>

	<?php endif; ?>

</div>
