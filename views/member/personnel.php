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
			<?php if (count((array) $squads)) : $i = 1;	?>



				<!-- get squads -->


				<?php foreach($squads as $squad) : ?>

					<?php $leader = Member::findById($squad->leader_id); ?>


					<a href='#collapseSquad_<?php echo $i; ?>' data-toggle='collapse' class='list-group-item active accordion-toggle' data-parent='#squads'>
						<?php if ($squad->leader_id != 0): ?>
							<?php echo ordsuffix($i) . " Squad - " . Rank::convert($leader->rank_id)->abbr ?> <?php echo $leader->forum_name ?> <span class="badge"><?php echo Squad::count($squad->id); ?></span>
						</a>

					<?php else: ?>
						<?php echo ordsuffix($i) ?> Squad <span class="badge"><?php echo Squad::count($squad->id); ?></span>
					<?php endif; ?>

					<!-- get squad members -->
					<div class='squad-group collapse' id='collapseSquad_<?php echo $i; ?>'>

						<?php foreach(Squad::find($squad->id) as $player) : ?>
							<a href='member/<?php echo $player->member_id ?>' class='list-group-item'><input type='checkbox' data-id='<?php echo $player->member_id; ?>' class='pm-checkbox'><span class='member-item'><?php echo $player->rank ?> <?php echo $player->forum_name ?></span><small class='pull-right text-<?php echo inactiveClass($player->last_activity); ?>'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></small></a>
						<?php endforeach; ?>
					</div>

					<?php $i++; ?>
				<?php endforeach;  ?>

			<?php endif; ?>
		</div>
		<div class='panel-footer'><button id='pm-checked' class='btn btn-success btn-sm toggle-pm pull-right' style='display: none;'>Send PM (<span class='count-pm'>0</span>)</button>  <button class='btn btn-default btn-sm toggle-pm pull-right'>PM MODE</button><div class='clearfix'></div></div>
	</div>

<?php endif; ?>