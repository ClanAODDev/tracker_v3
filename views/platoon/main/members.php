<div class='panel panel-default'>
	<div class='panel-heading'><div class='download-area hidden-xs'></div>Platoon members (last 30 days)<span></span></div>
	<div class='panel-body border-bottom'><div id='playerFilter'></div>
</div> 

<div class='table-responsive'>
	<table class='table table-striped table-hover' id='members-table'>
		<thead>
			<tr>
				<th><b>Member</b></th>
				<th class='nosearch text-center hidden-xs hidden-sm'><b>Rank</b></th>
				<th class='text-center hidden-xs hidden-sm'><b>Joined</b></th>
				<th class='text-center'><b>Last Active</b></th>
				<th class='text-center tool' title='In AOD servers'><b>AOD</b></th>
				<th class='text-center'><b>Overall</b></th>
				<th class='col-hidden'><b>Rank Id</b></th>
				<th class='col-hidden'><b>Last Login Date</b></th>
			</tr>
		</thead>
		<tbody>

			<?php foreach ($members as $member) : ?>


<!-- 				
$total_games = count_total_games($row['member_id'], $first_date_in_range, $last_date_in_range);
$aod_games = count_aod_games($row['member_id'], $first_date_in_range, $last_date_in_range);
$percent_aod = ($aod_games > 0 ) ? (($aod_games)/($total_games))*100 : NULL;
$percent_aod = number_format((float)$percent_aod, 2, '.', '');
$overall_aod_games[] = $aod_games;
$overall_aod_percent[] = $percent_aod;
$rank = $row['rank'];
$joindate = date("M Y", strtotime($row['join_date']));
$lastActive = formatTime(strtotime($row['last_activity']));
$status = lastSeenColored($lastActive); -->

				<tr data-id='<?php echo $member->member_id; ?>'>
					<td><em><?php echo memberColor(ucwords($member->forum_name), $member->position_id); ?></em></td>
					<td class='text-center hidden-xs hidden-sm'><?php echo $member->rank ?></td>

					<td class='text-center hidden-xs hidden-sm'><?php echo date('M Y', strtotime($member->join_date)); ?></td>
					<td class='text-center text-{$status}'><?php echo formatTime(strtotime($member->last_activity)); ?></td>

					<td class='text-center'>{$aod_games}</td>
					<td class='text-center'>{$total_games}</td>

					<td class='text-center col-hidden'><?php echo $member->rank_id ?></td>
					<td class='text-center col-hidden'><?php echo $member->last_activity ?></td>
				</tr>
			<?php endforeach; ?>


		</tbody>
	</table>
</div>
<div class='panel-footer text-muted text-center' id='member-footer'></div>