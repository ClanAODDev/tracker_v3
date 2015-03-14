<?php if ($user->role == 1) : ?><!-- if squad leader -->

	<div class='panel panel-default'>
		<div class='panel-heading'><strong> Your Squad</strong> {$squadCount}<span class='pull-right text-muted'>Last seen</span></div>

		<div class='list-group' id='squad'>
			<?php if (count($squad)) : ?>
				<?php foreach($squad as $member) : ?>
					<a href='/member/<?php echo $member->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $member->member_id ?>' style='margin-right: 10px; display: none;'><?php echo $member->rank ?> <?php echo $member->forum_name ?><small class='pull-right text-{$status}'><?php echo $member->last_seen ?></small></a>
				<?php endforeach; ?>				
			<?php else : ?>
				<p class='list-group-item'>It looks like you don't have any squad members assigned to you.</p>
			<?php endif; ?>
		</div>
		<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
	</div>

	
<?php elseif ($user->role == 2) : ?><!-- if platoon leader -->

	<div class='panel panel-default'>
		<div class='panel-heading'><strong> Your Platoon</strong> {$platoonCount}<span class='pull-right text-muted'>Last seen</span></div>

		<div class='list-group' id='squads'>
			{$my_platoon}
		</div>
		<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
	</div>

<?php endif; ?>



<!-- 
// squad leader personnel view
if ($userRole == 1) {
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
<a href='/member/{$id}' class='list-group-item'><input type='checkbox' data-id='{$id}' style='margin-right: 10px; display: none;'>{$rank} {$name}<small class='pull-right text-{$status}'>{$last_seen}</small></a>
";
}
} else {
$my_squad .= "<div class='panel-body'>Unfortunately it looks like you don't have any squad members!</div>";
}


// platoon leader personnel view
} else if ($userRole == 2) {
$squad_leaders = get_squad_leaders($user_game, $user_platoon);
$platoonCount = ($squad_leaders) ? "(" . count(get_platoon_members($user_platoon)) . ")" : NULL;

$i = 1;

if ($platoonCount) {

foreach ($squad_leaders as $squad_leader) {

$rank = $squad_leader['rank'];
$name = ucwords($squad_leader['name']);
$squad_members = get_my_squad($squad_leader['member_id']);
$last_seen = formatTime(strtotime($squad_leader['last_activity']));
$status = lastSeenColored($last_seen);
$squadCount = count($squad_members);

$my_platoon .= "
<a href='#collapseSquad{$i}' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>{$rank} {$name} ({$squadCount})</a>
<div class='squad-group collapse' id='collapseSquad{$i}'>";

	foreach ($squad_members as $squad_member) {
	$rank = $squad_member['rank'];
	$id = $squad_member['member_id'];
	$name = ucwords($squad_member['forum_name']);
	$last_seen = formatTime(strtotime($squad_member['last_activity']));
	$status = lastSeenColored($last_seen);

	$my_platoon .= "<a href='/member/{$id}' class='list-group-item'><input type='checkbox' data-id='{$id}' class='pm-checkbox'><span class=' member-item'>{$rank} {$name}</span><small class='pull-right text-{$status}'>{$last_seen}</small></a>";
}

$my_platoon .= "</div>";
$i++;

}


// add general population to list items
$gen_pop = get_gen_pop($user_platoon);
$genPopCount = count($gen_pop);
$my_platoon .= "
<a href='#collapseSquad{$i}' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>General Population ({$genPopCount})</a>
<div class='squad-group collapse' id='collapseSquad{$i}'>";

	foreach ($gen_pop as $gen_member) {
	$rank = $gen_member['rank'];
	$id = $gen_member['member_id'];
	$name = ucwords($gen_member['forum_name']);
	$last_seen = formatTime(strtotime($gen_member['last_activity']));
	$status = lastSeenColored($last_seen);

	$my_platoon .= "<a href='/member/{$id}' class='list-group-item'><input type='checkbox' data-id='{$id}' class='pm-checkbox'><span class='member-item'>{$rank} {$name}</span><small class='pull-right text-{$status}'>{$last_seen}</small></a>";
}
$my_platoon .= "</div>";


} else {
$my_platoon .= "<div class='panel-body'>Unfortunately it looks like you don't have any platoon members!</div>";
}

} -->