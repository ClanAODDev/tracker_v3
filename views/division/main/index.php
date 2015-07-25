<?php if (count($division)) : ?>

	<div class='container'>
		<ul class='breadcrumb'>
			<li><a href='./'>Home</a></li>
			<li class='active'><?php echo $division->full_name; ?></li>
		</ul>

		<div class='page-header'>
			
			<h2>

				<strong><img src='assets/images/game_icons/48x48/<?php echo $division->short_name; ?>.png' /> <?php echo $division->full_name; ?> Division</strong>

				<?php if ($user->role >= 3 || User::isDev()) : ?>
					<div class="btn-group pull-right">
						<a class="btn btn-default edit-div disabled" href="#" target="_blank"><i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm">Edit Division</span></a>
						<a class="btn btn-default create-div disabled" href="#" target="_blank"><i class="fa fa-plus-square"></i> <span class="hidden-xs hidden-sm">Create Division</span></a>
					</div>
				<?php endif; ?>

			</h2>

		</div>
		
		<p><?php echo $division->description; ?></p><hr>
		
		<div class='row'>
			<div class='col-md-8'>
				<div class='panel panel-primary'>
					<div class='panel-heading'>Currently Active <?php echo Locality::run('Platoons', $division->id); ?></div>
					<div class='list-group'>
						<?php $platoons = Platoon::find_all($division->id); ?>
						<?php if (count($platoons)) : ?>
							<?php foreach ($platoons as $platoon) : ?>
								<?php $leader = Member::findByMemberId($platoon->leader_id); ?>
								<a href='divisions/<?php echo $division->short_name; ?>/platoon/<?php echo $platoon->number; ?>' class='list-group-item'>
									<h5 class='pull-right text-muted big-num'><?php echo $platoon->number ?></h5>
									<h4 class='list-group-item-heading'><strong><?php echo $platoon->name; ?></strong></h4>
									<p class='list-group-item-text text-muted'><?php echo ($platoon->leader_id != 0) ? Rank::convert($leader->rank_id)->abbr . " " . $leader->forum_name : "TBA"; ?></p>
								</a>
							<?php endforeach; ?>
						<?php else : ?>
							<li class='list-group-item'>No platoons currently exist for this division.</li>
						<?php endif; ?>

					</div>
				</div>
			</div>

			<div class='col-md-4'>
				<div class='panel panel-info'>
					<div class='panel-heading'>Division Command Staff</div>
					<?php if (count($division_leaders)) : ?>

						<?php foreach($division_leaders as $leader) : ?>
							<a href='member/<?php echo $leader->member_id; ?>' class='list-group-item'>
								<h5 class='pull-right'><i class='fa fa-shield fa-2x text-muted'></i></h5>
								<h4 class='list-group-item-heading'><strong><?php echo Rank::convert($leader->rank_id)->abbr . " " . $leader->forum_name; ?></strong></h4>
								<p class='list-group-item-text text-muted'><?php echo $leader->position_desc; ?></p>
							</a>
						<?php endforeach; ?>

					<?php else : ?>
						<li class='list-group-item'>No leadership currently exists for this division.</li>
					<?php endif; ?>
				</div>
			</div>

		</div>
		
		<?php echo $statistics ?>

	</div>

<?php else : Flight::redirect('./404'); endif; ?>

