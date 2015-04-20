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