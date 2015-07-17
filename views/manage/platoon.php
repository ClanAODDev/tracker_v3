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

	<p>Platoons currently have at a max two squads. Ensure that your platoon membership is evenly distributed between your squads, and also avoid stacking all of your higher ranks into one particular squad. Organization of platoons may be primarily administrative, it does not hurt to try and build cohesion between squad-mates and between squads.</p>

	<div class="margin-top-50"></div>

	<div class="alert alert-info"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-exclamation fa-stack-1x  text-info"></i></span> <strong>TIP:</strong> Drag members between lists to organize them</div>

	<div class="row mod-plt">

		<?php $i=1; foreach ($squads as $squad): ?>

		<?php $leader = ($squad->leader_id != 0) ? arrayToObject(Member::findById($squad->leader_id)) : NULL; ?>
		<?php $leader_name = (!is_null($leader)) ? Rank::convert($leader->rank_id)->abbr . " " . $leader->forum_name : "TBA"; ?>
		<?php $members = Squad::members($squad->id); ?>

		<?php $squad_width = (count((array) $unassignedMembers)) ? 4 : 6; ?>

		<div class="col-xs-<?php echo $squad_width ?>">
			<h3 class="page-header squad-header"><strong><?php echo ordsuffix($i); ?> <?php echo Locality::run('Squad', $division->id); ?></strong> <small><?php echo $leader_name ?></small> <a href="#" class="btn btn-sm btn-default modify-squad"><i class="fa fa-wrench"></i> Edit Squad</a><span class="badge pull-right"><?php echo count((array)$members); ?></span></h3>

			<ul class="list-group sortable" data-squad-id="<?php echo $squad->id ?>" data-platoon-id="<?php echo $platoon->id ?>" data-division-id="<?php echo $division->id ?>">
				<?php foreach ($members as $member): ?>
					<?php $rctFlag = (!is_null($leader) && $member->recruiter == $leader->member_id) ? "<sup><i class='fa fa-asterisk text-success'></i></sup>" : NULL; ?>
					<li class="list-group-item" data-member-id="<?php echo $member->id ?>">
						<img src="assets/images/grab.svg" class="pull-right" style="width: 8px; opacity: .20;">
						<?php echo Rank::convert($member->rank_id)->abbr . " " . ucwords($member->forum_name) . $rctFlag; ?>
					</li>
				<?php endforeach; ?>
			</ul>

		</div>

		<?php $i++; endforeach;  ?>


		<?php if ($i < (MAX_SQUADS_IN_PLT + 1)): ?>
			<div class="col-xs-4">
				<h3 class="page-header text-muted"><strong>New <?php echo Locality::run('Squad', $division->id); ?></strong></h3>
				<p>Would you like to add a new <?php echo Locality::run('Squad', $division->id); ?>?</p><p><a data-platoon-id="<?php echo $platoon->id ?>" data-division-id="<?php echo $division->id ?>" class="btn btn-success center-block create-squad" href="create/squad"><i class="fa fa-plus"></i> Add</a></p>

			</div>
		<?php endif; ?>

		<?php $unassigned = count((array) $unassignedMembers); ?>

		<?php if ($unassigned): ?>
			<div class="col-xs-4 genpop">
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


	<hr>
	<small class="text-muted margin-top-10"><i class='fa fa-asterisk text-success'></i> - Direct recruit</small>
	

</div>
