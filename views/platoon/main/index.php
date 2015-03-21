
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
			<?php echo $statistics; ?>
		</div>

		<div class='col-md-8'>			
			<?php echo $membersTable; ?>
		</div>
	</div>
</div>

<!-- if platoon not found -->
<?php else : ?>

	<?php header('Location: /404/'); ?>

<?php endif; ?>

