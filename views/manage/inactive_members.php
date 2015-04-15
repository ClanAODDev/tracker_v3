<div class='container'>

	<input type="hidden" id="member_id" value="<?php echo $member->member_id ?>"></input>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Manage inactive players</li>
	</ul>


	<div class='page-header'>
		<h2><strong>Manage <small>Inactive Members</small></strong></h2>
	</div>

	<p>Inactive members are pruned on a monthly basis. Use this tool to manage members who are considered inactive, that is, their last forum activity (login or otherwise) exceeds 30 days. In order to ensure your subordinate members receive fair warning, you must <strong>make every possible attempt</strong> to get this user back in good standing with the clan. Once all efforts have been exhausted, flag the member for removal by adding them to the 'flag for removal' list. </p>

	<p>A member who returns, or corrects their inactivity will automatically be removed from this list, as long as they return before the end of the clean-up.</p>

	<div class='margin-top-50'></div>		
	<div class='page-header'>
		<h3>List Management
			<small class='text-muted'> To flag a member, drag them from the inactive list to the 'flagged' list.</small></h3>
		</div>

		<?php if (count($inactiveCount) || count($flaggedCount)) : ?>

			<?php if (count($flaggedCount)) : ?>

				<div class='row flagged-section'>
					<div class='col-md-12'>
						<div class='panel panel-danger'>
							<div class='panel-heading'><i class='fa fa-trash-o fa-lg'></i> Members flagged for removal <span class='flagCount pull-right badge'><?php echo $flaggedCount ?></span></div>
							<ul class='sortable striped-bg' id='flagged-inactives' style='overflow-y: auto; max-height: 193px;'>

								<?php $flaggedCopy = "[SIZE=2]Members flagged for removal ({$flaggedCount})[/SIZE][hr][/hr][table=\"width: 500\"]"; ?>
								<?php $flaggedCopy .= "[tr][td][b]Player[/b][/td][td][b]Forum Account[/b][/td][td][b]Battlelog Account[/b][/td][/tr]"; ?>

								<?php foreach ($flagged as $player) : ?>
																		
									<?php $flaggedCopy .= "[tr][td][COLOR=\"#FFD700\"]{$player->forum_name}[/color][/td][td][url=" . CLANAOD . "{$player->member_id}]Forum Account[/url][/td][td][url=" . BATTLELOG . "{$player->battlelog_name}]Battlelog Account[/url][/td][/tr]"; ?>
									

									<li class='list-group-item clearfix' data-user-id='<?php echo $player->member_id ?>' data-player-id='<?php echo $player->member_id ?>'>
										<div class='col-xs-1'><img src='assets/images/grab.svg' style='width: 8px; opacity: .20;' /></div>
										<div class='col-xs-2'><?php echo $player->rank ?> <?php echo $player->forum_name ?></div>
										<div class='col-xs-3 text-muted text-center'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></div>
										<div class='col-xs-3 removed-by text-center text-muted'>Flagged by <?php echo $player->flagged_by ?></div>
										<div class='col-xs-3 actions btn-group'>
											<span class='pull-right'>
												<a href='http://www.clanaod.net/forums/private.php?do=newpm&u=<?php echo $player->member_id ?>' class='popup-link btn btn-default btn-xs'><i class='fa fa-comment'></i> PM</a> 
												<button class='btn btn-default btn-xs view-profile'><i class='fa fa-user'></i> View Profile</button>
												<?php if ($user->role >= 2 && $member->rank_id >= 9) : ?>
													<a href="# <?php echo REMOVE . $player->member_id ?>" title="Remove player from AOD" class='removeMember btn btn-danger btn-xs'><i class='fa fa-times'></i> Remove</a>
												<? endif; ?> 
											</span> 
										</div>
									</li>

								<?php endforeach; ?>
								<?php $flaggedCopy .= "[/table]"; ?>

							</ul>
							<div class='panel-footer clearfix'><button type='button' class='copy-button btn btn-default tool pull-right' title='Copy to clipboard' data-clipboard-text='<?php echo $flaggedCopy ?>'><i class='fa fa-copy'></i> Copy player list</button>
							</div>
						</div>
					</div>

				</div>

			<?php else : ?>
				<div class='row'>
					<div class='col-md-12'>
						<div class='panel panel-danger'>
							<div class='panel-heading'><i class='fa fa-trash-o fa-lg'></i> Members flagged for removal <span class='flagCount pull-right badge'><?php echo $flaggedCount ?></span></div>
							<ul class='sortable striped-bg' id='flagged-inactives' style='overflow-y: auto; max-height: 193px;'>
							</ul>
						</div>
					</div>
				</div>

			<?php endif; ?>

			<div class='row inactives-section'>
				<div class='col-md-12'>

					<div class='panel panel-info'>
						<div class='panel-heading'><i class='fa fa-clock-o fa-lg'></i> Your inactive members <span class='inactiveCount pull-right badge'><?php echo $inactiveCount ?></span></div>
						<ul class='sortable inactive-list striped-bg' id='inactives' style='overflow-y: auto; max-height: 193px;'>
							<?php $inactive_ids = array(); ?>

							<?php $inactiveCopy = "[SIZE=3]Inactive members ({$inactiveCount})[/SIZE][hr][/hr][table=\"width: 700\"]"; ?>
								<?php $inactiveCopy .= "[tr][td][b]Player[/b][/td][td][b]Forum Account[/b][/td][td][b]Battlelog Account[/b][/td][td][b]Last Seen[/b][/td][td][b]Platoon[/b][/td][/tr]"; ?>

							<?php foreach ($inactives as $player) : ?>
								<?php $inactive_ids[] = $player->member_id; ?>

								<?php $inactiveCopy .= "[tr][td][COLOR=\"#FFD700\"]{$player->forum_name}[/color][/td][td][url=" . CLANAOD . "{$player->member_id}]Forum Account[/url][/td][td][url=" . BATTLELOG . "{$player->battlelog_name}]Battlelog Account[/url][/td][td]" . formatTime(strtotime($player->last_activity)) . "[/td][td]" . ordSuffix($player->plt_number) . " Platoon[/td][/tr]"; ?>

								<li class='list-group-item clearfix' data-user-id='<?php echo $player->member_id ?>' data-player-id='<?php echo $player->member_id ?>'>
									<div class='col-xs-1'><img src='/public/images/grab.svg' style='width: 8px; opacity: .20;' /></div>
									<div class='col-xs-2'><?php echo $player->rank ?> <?php echo $player->forum_name ?></div>
									<div class='col-xs-3 text-muted text-center'>Seen <?php echo formatTime(strtotime($player->last_activity)); ?></div>
									<div class='col-xs-3 removed-by text-center text-muted'></div>
									<div class='col-xs-3 actions btn-group'><span class='pull-right'><a href='http://www.clanaod.net/forums/private.php?do=newpm&u=<?php echo $player->member_id ?>' class='popup-link btn btn-default btn-xs'><i class='fa fa-comment'></i> PM</a> <button class='btn btn-default btn-xs view-profile'><i class='fa fa-user'></i> View Profile</button></span> 
									</div>
								</li>

							<?php endforeach; ?>

							<?php $inactiveCopy .= "[/table]"; ?>

							<?php $inactive_ids = implode("&u[]=", $inactive_ids); ?>

						</ul>
						<div class='panel-footer clearfix'><a href='http://www.clanaod.net/forums/private.php?do=newpm&u[]=<?php echo $inactive_ids ?>' class='mass-pm-btn pull-right popup-link btn btn-default'><i class='fa fa-users'></i> Mass PM Players</a> <button type='button' class='copy-button btn btn-default tool pull-right' title='Copy to clipboard' data-clipboard-text='<?php echo $inactiveCopy ?>'><i class='fa fa-copy'></i> Copy player list</button></div>
					</div>
				</div>
			</div>

		<?php else : ?>
			<div class='row margin-top-50'>
				<div class='col-md-12 '>
					<div class='panel panel-success'>
						<div class='panel-heading'><i class='fa fa-check'></i> Congratulations! <span class='inactiveCount pull-right badge'>{$inactiveCount}</span></div>
						<ul class='striped-bg'>
							<li class='list-group-item'>None of your members are currently inactive!</li>
						</ul>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>