<div class='container'>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href="divisions/<?php echo $division->short_name ?>"><?php echo $division->full_name ?></a></li>
		<li><a href="divisions/<?php echo $division->short_name ?>/platoon/<?php echo $platoon->number ?>"><?php echo $platoon->name ?></a></li>
		<li class='active'>Manage Members</li>
	</ul>

	<div class='page-header'>
		<h2><strong>Manage <small><?php echo $platoon->name ?></small></strong></h2>
	</div>

	<?php $squadCount = count((array)Squad::findAll($division->id, $platoon->id)); ?>

	<?php if ($squadCount): ?>


		<div class="row mod-plt">


			<div class="col-xs-8">
				<div class="row">
					
					<?php $i=1; ?>
					<?php foreach ($squads as $squad): ?>

						<?php $leader = ($squad->leader_id != 0) ? arrayToObject(Member::findById($squad->leader_id)) : NULL; ?>
						<?php $leader_name = (!is_null($leader)) ? $leader->forum_name : "TBA"; ?>
						<?php $members = Squad::members($squad->id); ?>

						<div class="col-xs-6">
							<h3 class="page-header squad-header"><?php echo ordsuffix($i); ?> <?php echo Locality::run('Squad', $division->id); ?> <small><?php echo $leader_name ?></small> <a href="#" class="btn btn-xs btn-default modify-squad" style="position: absolute; left: 15px;"><i class="fa fa-wrench"></i> Edit Squad</a><span class="badge pull-right"><?php echo count((array)$members); ?></span></h3>

							<ul class="list-group sortable" data-squad-id="<?php echo $squad->id ?>" data-platoon-id="<?php echo $platoon->id ?>" data-division-id="<?php echo $division->id ?>">
								<?php foreach ($members as $member): ?>
									<?php $rctFlag = (!is_null($leader) && $member->recruiter == $leader->member_id) ? "<sup><i class='fa fa-asterisk text-success'></i></sup>" : NULL; ?>
									<li class="list-group-item" data-member-id="<?php echo $member->id ?>">
										<img src="assets/images/grab.svg" class="pull-right" style="width: 8px; opacity: .20;">
										<?php echo ucwords($member->forum_name) . $rctFlag; ?>
									</li>
								<?php endforeach; ?>
							</ul>

						</div>

						<?php if ($i % 2 ==0): ?>
							<div style="clear: both; height: 60px;"></div>
						<?php endif; ?>
						<?php $i++; ?>

					<?php endforeach;  ?>
				</div>

			</div>

			<div class="col-xs-4">
				<div class="row">

					<?php if ($squadCount < (MAX_SQUADS_IN_PLT)): ?>
						<div class="col-xs-12">
							<div class="panel panel-info">
								<div class="panel-heading"><strong>New <?php echo Locality::run('Squad', $division->id); ?></strong></div>
								<div class="panel-body">
									<p>Would you like to add a new <?php echo Locality::run('Squad', $division->id); ?>?  <a data-platoon-id="<?php echo $platoon->id ?>" data-division-id="<?php echo $division->id ?>" class="btn btn-success btn-xs create-squad pull-right" href="create/squad"><i class="fa fa-plus"></i> Add</a></p>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php $unassigned = count((array) $unassignedMembers); ?>
					<?php if ($unassigned): ?>
						<div class="col-xs-12 genpop">
							<h3 class="page-header text-muted"><strong>Unassigned</strong><span class="badge pull-right"><?php echo $unassigned; ?></span></h3>

							<ul class="list-group sortable">
								<?php foreach ($unassignedMembers as $member): ?>
									<li class="list-group-item" data-member-id="<?php echo $member->id ?>">
										<img src="assets/images/grab.svg" class="pull-right" style="width: 8px; opacity: .20;">
										<?php echo Rank::convert($member->rank_id)->abbr . " " . ucwords($member->forum_name); ?>
									</li> 
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>

	<?php else: ?>
		<p>There aren't any members currently assigned here. Edit a member's profile to assign them here, and you will then be able to organize appropriately.</p>
	<?php endif; ?>

</div>
