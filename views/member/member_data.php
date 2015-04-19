<div class='panel panel-info'>
	<div class='panel-heading'><strong>Member Information</strong></div>
	<ul class='list-group'>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Status: </strong></span> <span class='text-muted'><?php echo $memberInfo->desc ?></span></li>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Division: </strong></span> <span class='text-muted'><?php echo $divisionInfo->full_name ?></span></li>
		<?php echo $platoonInfo->item ?>
		<li class='list-group-item text-right'><span class='pull-left'><strong>Position: </strong></span> <span class='text-muted'><?php echo $memberInfo->position ?></span></li>
		<?php $squadleader = ($memberInfo->squad_leader_id != 0) ? $memberInfo->squad_leader_id : NULL; ?>

		<?php if (!is_null($squadleader)) : ?>
			<a href="member/<?php echo $squadleader ?>" class="list-group-item text-right">
				<span class='pull-left'><strong>Squad Leader: </strong></span> 
				<span class='text-muted'><?php echo Member::findForumName($squadleader) ?></a></span>
			</a>
		<?php endif; ?>

		<?php $recruiter = ($memberInfo->recruiter != "0") ? $memberInfo->recruiter : NULL; ?>
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

	<a target='_blank' href='<?php echo CLANAOD . $memberInfo->member_id ?>' class='list-group-item'>AOD Forum <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>

	<?php if ($memberInfo->battlelog_name !== "0") : ?>
		<a target="_blank" href="<?php echo BATTLELOG . $memberInfo->battlelog_name ?>" class="list-group-item">Battlelog <span class='pull-right'><i class='text-info fa fa-external-link'></i></span></a>
	<?php endif; ?>

</div>