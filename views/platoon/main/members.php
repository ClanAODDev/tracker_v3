<div class='panel panel-default'>
	<div class='panel-heading'><div class='download-area hidden-xs'></div>Platoon members (last 30 days)<span></span></div>
	<div class='panel-body border-bottom'><div id='playerFilter'></div>
</div> 

<div class='table-responsive'>
	<table class='table table-striped table-hover' id='members-table'>
		<thead>
			<tr>
				<th class='col-hidden'><b>Rank Id</b></th>
				<th class='col-hidden'><b>Last Login Date</b></th>
				<th><b>Member</b></th>				
				<th class='nosearch text-center hidden-xs hidden-sm'><b>Rank</b></th>
				<th class='text-center hidden-xs hidden-sm'><b>Joined</b></th>
				<th class='text-center'><b>Last Active</b></th>

				<?php if ($division->id == 2): ?>
					<th class='text-center tool' title='In AOD servers'><b>AOD</b></th>
					<th class='text-center'><b>Overall</b></th>	
				<?php endif; ?>			
			</tr>
		</thead>
		<tbody>

			<?php foreach ($members as $member) : ?>
				<tr title='Click to view profile' data-id='<?php echo $member->member_id; ?>'>
					<td class='text-center col-hidden'><?php echo $member->rank_id ?></td>
					<td class='text-center col-hidden'><?php echo $member->last_activity ?></td>
					<td><em><?php echo memberColor(ucwords($member->forum_name), $member->position_id); ?></em></td>
					<td class='text-center hidden-xs hidden-sm'><?php echo Rank::convert($member->rank_id)->abbr; ?></td>
					<td class='text-center hidden-xs hidden-sm'><?php echo date('m-d-y', strtotime($member->join_date)); ?></td>
					<td class='text-center text-<?php echo lastSeenColored($member->last_activity); ?>'><?php echo formatTime(strtotime($member->last_activity)); ?></td>

					<?php if ($division->id == 2): ?>
						<td class='text-center'><?php echo Activity::countPlayerAODGames($member->member_id, $bdate, $edate); ?></td>
						<td class='text-center'><?php echo Activity::countPlayerGames($member->member_id, $bdate, $edate); ?></td>
					<?php endif; ?>
					
				</tr>
			<?php endforeach; ?>

		</tbody>
	</table>

	<div class='panel-footer text-muted text-center' id='member-footer'></div>
</div>

</div>