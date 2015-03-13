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
			<?php if (count($notifications)) : ?>
				<?php foreach($notifications as $notification) { echo $notification; } ?>
			<?php endif; ?>
			<?php if (count($alerts)) : ?>
				<?php foreach($alerts as $alert) : ?>
					<div data-id="<?php echo $alert->id; ?>" data-user="<?php echo $user->id; ?>" class="alert-dismissable alert alert-<?php echo $alert->type; ?> fade in" role="alert">
						<button type="button" class="close" data-dismiss="alert">
							<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
						</button>
						<?php echo $alert->content; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- main division list -->
	<div class='row'>
		<div class='col-md-12'>
			<div class='panel panel-default'>
				<div class='panel-heading'><i class='fa fa-gamepad fa-lg pull-right text-muted'></i> <strong>Gaming Divisions</strong></div>
				<div class='list-group'>
					<?php foreach ($divisions as $division) : ?>
						<a href='divisions/<?php echo $division->short_name ?>' class='list-group-item' style='padding-bottom: 18px;'>
							<span class='pull-left' style='margin-right: 20px; vertical-align: middle;'><img src='assets/images/game_icons/large/<?php echo $division->short_name ?>.png' /></span>
							<h4 class='list-group-item-heading'><strong><?php echo $division->full_name ?></strong></h4>
							<p class='list-group-item-text text-muted'><?php echo $division->description ?></p>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
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

		{$posts}

	<?php else : ?>

		<!-- quick tools and personnel view-->
		<div class='row'>
			<div class='col-md-5'>
				<div class='panel panel-primary'>
					<div class='panel-heading'><strong><?php echo getUserRoleName($user->role); ?> Quick Tools</strong></div>
					<div class='list-group'>
						<?php if (count($tools)) : ?>
							<?php foreach($tools as $tool) : ?>
								<a href='<?php echo $tool->tool_path ?>' class='list-group-item <?php echo $tool->class ?>'>
									<h4 class='pull-right text-muted'><i class='fa fa-<?php echo $tool->icon ?> fa-lg'></i></h4>
									<h4 class='list-group-item-heading'><strong><?php echo $tool->tool_name ?></strong></h4>
									<p class='list-group-item-text text-muted'><?php echo $tool->tool_descr ?></p>
								</a>			
							<?php endforeach; ?>				
						<?php else : ?>
							<li class='list-group-item'>No tools currently available to you</li>
						<?php endif; ?>
					</div>
				</div>

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
				<?php if (count($posts)) : ?>
					<?php foreach($posts as $post) : ?>
						<div class='panel panel-default'>
							<div class='panel-heading'><?php echo Member::avatar($post->forum_id) .  $post->title; ?></div>
							<div class='panel-body'><?php echo $post->content ?></div>
							<div class='panel-footer text-muted text-right'>
								<small>Posted <?php echo date("Y-m-d", strtotime($post->date)); ?> by <a href='/member/{$authorId}'><?php echo Member::find_forum_name($post->forum_id) ?></a></small>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

	<?php endif; ?>

</div>
