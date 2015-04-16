<?php if ($user->role == 1) : ?><!-- if squad leader -->

	<?php if (Squad::count($member->member_id)) : ?>
		<div class='panel panel-default'>
			<div class='panel-heading'><strong> Your Squad</strong> <span class="pull-right"><?php echo Squad::count($member->member_id); ?> members</span></div>
			<div class='list-group' id='squad'>
				<?php foreach($squad as $player) : ?>
					<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo $player->rank ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>
				<?php endforeach; ?>				
			</div>
			<div class='panel-footer'>
				<button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div>
			</div>
		</div>

	<?php endif; ?>
	
<?php elseif ($user->role == 2) : ?><!-- if platoon leader -->

	<div class='panel panel-default'>
		<div class='panel-heading'><strong> Your Platoon</strong> <span class=" pull-right"><?php echo Platoon::countPlatoon($member->platoon_id); ?> members</span></div>
		<div class='list-group' id='squads'>
			<?php if (count(Platoon::countSquadLeaders($member->platoon_id))) : $i = 0;	?>

				<!-- get squad leaders-->
				<?php foreach(Platoon::SquadLeaders($member->game_id, $member->platoon_id) as $player) :?>
					<a href='#collapseSquad_<?php echo $i; ?>' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>
						<?php echo $player->abbr ?> <?php echo $player->forum_name ?> <span class="badge"><?php echo Squad::count($player->member_id); ?></span>
					</a>

					<!-- get squad members -->
					<div class='squad-group collapse' id='collapseSquad_<?php echo $i; ?>'>
						
						<?php foreach(Squad::find($player->member_id) as $player) : ?>
							<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo $player->rank ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>
						<?php endforeach; ?>
					</div>

					<?php $i++; ?>
				<?php endforeach;  ?>

				<!-- get general population -->
				<a href='#collapseSquad_<?php echo $i; ?>' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>General Population <span class="badge"><?php echo Platoon::countGeneralPop($member->platoon_id); ?></span></a>
				<div class='squad-group collapse' id='collapseSquad_<?php echo $i; ?>'>

					<?php foreach($genPop as $player) : ?>
						<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo $player->rank ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>
					<?php endforeach; ?>

				</div>

			<?php endif; ?>
		</div>
		<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
	</div>

<?php endif; ?>






























<!-- 









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