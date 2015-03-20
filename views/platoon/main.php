
<?php if ($platoon_id = Platoon::get_id_from_number($plt, $div)) : ?>
	

	<?php if ($user->role == 1 && $platoon_id == $user_platoon) : ?>

		$squad_members = get_my_squad($forumId);
		$squadCount = ($squad_members) ? "(" . count($squad_members) . ")" : NULL;

		if ($squad_members) {

		foreach ($squad_members as $squad_member) {
		$name = ucwords($squad_member['forum_name']);
		$id = $squad_member['member_id'];
		$rank = $squad_member['rank'];
		$last_seen = formatTime(strtotime($squad_member['last_activity']));

		// visual cue for inactive squad members
		if (strtotime($last_seen) < strtotime('-30 days')) {
		$status = 'danger';
	} else if (strtotime($last_seen) < strtotime('-14 days')) {
	$status = 'warning';
} else {
$status = 'muted';
}

$my_squad .= "
<a href='/member/{$id}' class='list-group-item'>{$rank} {$name}<small class='pull-right text-{$status}'>{$last_seen}</small></a>
";
}

} else {
$my_squad .= "<div class='panel-body'>Unfortunately it looks like you don't have any squad members!</div>";
}




<?php endif; ?>





<!-- 





// calculate inactives, percentage
$min = INACTIVE_MIN;
$max = INACTIVE_MAX;


$inactive = array_filter(
$overall_aod_games,
function ($value) use($min,$max) {
return ($value >= $min && $value <= $max);
})
;


$inactive_count = count($inactive);
$inactive_percent = round((float)($inactive_count / $member_count) * 100 ) . '%';

// calculate overall percentages
$overall_aod_percent = array_diff($overall_aod_percent, array('0.00'));
$overall_aod_percent = array_sum($overall_aod_percent) / count($overall_aod_percent);
$overall_aod_games = array_sum($overall_aod_games);

 -->


<div class='container fade-in'>
	
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='divisions/<?php echo $division->short_name ?>'><?php echo $division->full_name ?></a></li>
		<li class='active'><?php echo $platoon->name ?></li>
	</ul>

	<div class='row page-header'>

		<div class='col-xs-7 platoon-name'>
			<h2><img src='assets/images/game_icons/large/<?php echo $division->short_name ?>.png' /> <strong><?php echo $platoon->name ?></strong> <small class='platoon-number'><?php echo ordSuffix($plt); ?> Platoon</small></h2>
		</div>

		<div class='col-xs-5'>
			<?php if ($user->role >= 2) : ?>

				<div class='btn-group pull-right'>
					<button type='button' class='btn btn-default disabled'>Edit</button>
					<a class='btn btn-default popup-link' href='http://www.clanaod.net/forums/private.php?do=newpm&amp;u[]=<?php echo implode("&u[]=", $memberIdList); ?>' target='_blank'><i class='fa fa-comment'></i> Send Platoon PM</a>
				</div>

			<?php endif; ?>
		</div>

	</div>

	<div class='row'>
		<div class='col-md-4 hidden-xs'>
			<div class='panel panel-default'>
				<div class='panel-heading'>Total Members</div>
				<div class='panel-body count-detail-big striped-bg'><span class='count-animated'><?php echo Platoon::countPlatoon($platoon->id); ?></span>
				</div>
			</div>

			<div class='panel panel-default'>
				<div class='panel-heading'>Percentage AOD Games</div>
				<div class='panel-body count-detail-big follow-tool striped-bg' title='Excludes all zero values'><span class='count-animated percentage'>{$overall_aod_percent}</span>
				</div>
			</div>

			<!-- show squad if squad leader in platoon being viewed -->
			<div class='panel panel-default'>
				<div class='panel-heading'><strong> Your Squad</strong> {$squadCount}<span class='pull-right text-muted'>Last seen</span></div>

				<div class='list-group' id='squad'>
					{$my_squad}
				</div>
			</div>
		</div>

		<div class='col-md-8'>			
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

							<tr data-id='{$row['member_id']}'>
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
		</div>
	</div>
</div>

<!-- if platoon not found -->
<?php else : ?>

	<?php header('Location: /404/'); ?>

<?php endif; ?>

