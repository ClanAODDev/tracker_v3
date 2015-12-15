<div class='panel panel-info'>
	<div class='panel-heading'><strong>Member Information</strong></div>
	<ul class='list-group'>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Status: </strong></span> <span class='text-muted'><?php echo Status::convert($memberInfo->status_id)->desc ?></span></li>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Division: </strong></span> <span class='text-muted'><?php echo $divisionInfo->full_name ?></span></li>

		<?php echo (property_exists($platoonInfo, 'item')) ? $platoonInfo->item : NULL; ?>

		<?php $position = ($memberInfo->position_id) ? Position::convert($memberInfo->position_id)->desc : 'Unknown'; ?>

		<li class='list-group-item text-right'><span class='pull-left'><strong>Position: </strong></span> <span class='text-muted'><?php echo Locality::run($position, $memberInfo->game_id)?></span></li>
		<?php $squadleader = (property_exists($memberInfo, 'squad_leader_id')) ? $memberInfo->squad_leader_id : NULL; ?>

		<?php if ($squadleader != 0) : ?>
			<a href="member/<?php echo $squadleader ?>" class="list-group-item text-right">
				<span class='pull-left'><strong>Squad Leader: </strong></span>
				<span class='text-muted'><?php echo Member::findForumName($squadleader) ?></a></span>
			</a>
		<?php endif; ?>

		<?php $recruiter = ($memberInfo->recruiter != 0) ? $memberInfo->recruiter : NULL; ?>
		<?php if (!is_null($recruiter)) : ?>
			<a href="member/<?php echo $recruiter ?>" class="list-group-item text-right">
				<span class='pull-left'><strong>Recruiter: </strong></span>
				<span class='text-muted'><?php echo Member::findForumName($recruiter) ?></a></span>
			</a>
		<?php endif; ?>

	</ul>
</div>

<div class='panel panel-info'>
	<div class='panel-heading'><strong>Forum Activity</strong></div>
	<ul class='list-group'>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Joined:</strong></span> <span class='text-muted'><?php echo date('Y-m-d', strtotime($memberInfo->join_date)); ?></span></li>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Last seen:</strong></span> <span class='text-muted'><?php echo formatTime(strtotime($memberInfo->last_activity)); ?></span></li>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Last posted:</strong></span>  <span class='text-muted'><?php echo formatTime(strtotime($memberInfo->last_forum_post)); ?></span></li>
	</ul>
</div>

<div class='panel panel-info'>
	<div class='panel-heading'>
		<strong>Gaming Profiles</strong>
	</div>

	<!-- everyone has a forum profile -->
	<a target='_blank' href='<?php echo CLANAOD . $memberInfo->member_id ?>' class='list-group-item'>AOD Forum <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>

	<?php foreach($aliases as $alias): ?>
		<?php if ($alias->isVisible): ?>
			<?php $invalid = ($alias->isInvalid) ? "<label class=\"label label-danger\" title=\"Invalid\"><i class=\"fa fa-times\"></i></label>": NULL; ?>
			<a target="_blank" href="<?php echo (property_exists($alias, 'url')) ? $alias->url . $alias->handle_value : "#"; ?>" class="list-group-item"><?php echo $invalid . $alias->name ?> <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>
		<?php endif; ?>
	<?php endforeach; ?>

</div>
