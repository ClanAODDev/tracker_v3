<?php $allowEdit = User::canUpdate($user->role); ?>

<div class='container'>
	
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='recruiting/'>Recruiting</a></li>
		<li class='active'>Add New Member</li>
	</ul>
	<div class='page-header'>
		<h1><strong>Recruiting</strong> <small>Add New Member <span class='text-warning'></span></small></h1>
	</div>

	<div id='rootwizard'>

		<!-- necessary for step functionality -->

		<div class='navbar guide-nav centered-pills' style='display: <?php echo (User::isDev()) ? "block" : "none"; ?>'>
			<div class='navbar-inner'>
				<ul>
					<li class='slide1'><a href='#tab1' data-toggle='tab'>Recruit Introduction</a></li>
					<li class='slide2'><a href='#tab2' data-toggle='tab'>Add Member Information</a></li>
					<li class='slide3'><a href='#tab3' data-toggle='tab'>Recruit Thread Completion</a></li>
					<li class='slide4'><a href='#tab4' data-toggle='tab'>Finish with Recruit</a></li>
					<li class='slide5'><a href='#tab5' data-toggle='tab'>Dreaded Paperwork</a></li>
					<li class='slide6'><a href='#tab6' data-toggle='tab'>Add to division</a></li>
				</ul>
			</div>
		</div>
		

		<div class='progress striped-bg'>
			<div class='bar progress-bar progress-bar-rct progress-bar-striped progress-bar-success active' ></div>
		</div>


		<div class='panel panel-default'>
			<div class='panel-heading tab-title'>
				<strong></strong><span class='pull-right text-muted'><?php echo $division->full_name ?> Division</span>
			</div>

			<div class='panel-body'>

				<form class='form-horizontal' id='new-recruit'>

					<input type='hidden' value='<?php echo $division->id ?>' id='game' name='game' />

					<div class='tab-content'>

						<!-- // tab 1 - introduction -->


						<div class='tab-pane' id='tab1'>
							<div class='col-xs-12'>
								<p>At this point, you have already established a potential recruit. The next step is to get him or her through AOD's recruiting process, and added to our division structure. If you have not already, you need to get your recruit into Teamspeak. Your relationship to your new recruit is vital and begins with the first impression. Get to know them and make them feel welcome. This will make a huge difference down the road.</p>

								<p class="margin-top-20 lead">Vital items to discuss:
									<hr />
									
									<ul>
										<li>You must <strong>be in Teamspeak</strong> whenever you're ingame.</li>
										<li>You must <strong>login to the forums</strong> at least once a month.</li>
										<li>You should strive to <strong>be a contributing member</strong> of the clan. This includes helping us populate the server, and staying loyal to our servers whenever possible.</li>
										<li>You must <strong>show respect</strong> to other members as well as all other public players.</li>
									</ul>

								</p>

								<p class="margin-top-50 lead"><strong>AOD</strong> Quick Info</p>
								<hr />

								<p><a class='popup-link'  href='http://www.clanaod.net/forums/showthread.php?t=97502'><button type='button' class='btn btn-primary'>Teamspeak Server Information</button></a> <a href='http://www.teamspeak.com/?page=downloads&gclid=CJakz7CwwcMCFRQQ7AodsDsASA' target='_blank'><button type='button' class='tool btn btn-primary' title='Right click to copy link'>TS3 Client Download</button></a>  <a href='http://www.clanaod.net/forums/showthread.php?t=72805' target='_blank' class='popup-link'><button type='button' class='tool btn btn-primary' title='Recruitment Process Thread'>Recruitment Process Thread</button></a></p>

							</div>
						</div>


						<!-- // tab 2 - member information form -->

						<div class='tab-pane' id='tab2'>

							<div class='col-sm-6'>
								<p class='margin-top-20'>Does your new recruit have a forum account? They will need one for you to complete this section. Please fill out and check the form completely for accuracy once this has been done. </p>
								<p class='text-warning'><strong>Be certain the Battlelog name is correct. If it is not valid, server activity will not be available for your new recruit.</strong></p>
							</div>
							<div class='col-sm-6 well'>
								<div class='form-group memberid-group'>
									<label for='member_id' class='col-sm-3 control-label'><strong>Forum ID</strong></label>
									<div class='col-sm-9'>
										<input type='text' class='form-control' placeholder='12345' id='member_id' name='member_id' tabindex='1'>
									</div>
								</div>

								<div class='form-group forumname-group'>
									<label for='forumname' class='col-sm-3 control-label'><strong>Forum Name</strong></label>
									<div class='col-sm-9'>
										<input type='text' class='form-control' placeholder='JoeSnuffy25' id='forumname' name='forumname' tabindex='2'>
									</div>
								</div>

								<div class='form-group ingame-group'>
									<label for='ingame' class='col-sm-3 control-label'><strong>Ingame Name</strong></label>
									<div class='col-sm-9'>
										<input type='text' class='form-control' placeholder='JoeSnuffy25' id='ingame' name='ingame' tabindex='3'>
									</div>
								</div>

								<div class='form-group platoon-group' style='display: <?php echo $allowEdit->pltField ?>'>
									<label for='platoon' class='col-sm-3 control-label'><strong><?php echo Locality::run('Platoon', $division->id); ?></strong></label>
									<div class='col-sm-9'>
										<select name='platoon' id='platoon' class='form-control'>
											<?php foreach($platoons as $platoon) : ?>
												<option value='<?php echo $platoon->id ?>'><?php echo $platoon->name ?></option>
											<?php endforeach; ?> 
										</select>
									</div>
								</div>

								<div class='form-group squadldr-group' style='display: <?php echo $allowEdit->sqdField ?>'>
									<label for='squad_id' class='col-sm-3 control-label'><strong><?php echo Locality::run('Squad leader', $division->id); ?></strong></label>
									<div class='col-sm-9'>
										<select name='squad_id' id='squad_id' class='form-control'>
											<?php foreach($squads as $squad) : ?>

												<?php $leader = Member::findById($squad->leader_id); ?>
												<?php $platoon = Platoon::findById($squad->platoon_id); ?>

												<option value='<?php echo $squad->id ?>'><?php echo ($squad->leader_id != 0) ? Rank::convert($leader->rank_id)->abbr . " " . ucwords($leader->forum_name) : "TBA (Squad #{$squad->id})"; ?> - <?php echo $platoon->name ?></option>

											<?php endforeach; ?>
										</select>
									</div>
								</div>


								<?php if (count(SubGame::count($member->game_id))): ?>
									<div class="form-group game-group">
										<label for='platoon' class='col-sm-3 control-label'><strong>Games Played</strong></label>
										<div class='col-sm-9'>
											<select id="games" multiple="multiple">
												<?php foreach (SubGame::get($member->game_id) as $game): ?>
													<option value="<?php echo $game->id ?>"><?php echo $game->full_name ?></option>
												<?php endforeach; ?>
											</select>
										</div>
									</div>
								<?php endif; ?>


								<div class='text-center message text-danger'></div>
							</div>

						</div>


						<!-- // tab 3 - Recruiting thread status check -->


						<div class='tab-pane' id='tab3'>
							<div class='col-sm-6'>

								<p class='margin-top-20'>Listed are the recruiting threads required for each of your division's members to read and respond to. The status indicates whether or not your new recruit has made a post in each of those threads (<em>checking last 5 pages of a thread ensures we don't miss a post</em>).</p><p>Use the 'copy' button next to each link to copy the link to your clipboard, and be sure to take the time to explain each of these threads, hitting the high (important) notes. Ensure each thread is completed (and that they understand them), before continuing.</p>

							</div>
							<div class='col-sm-6 well'>

								<div class='search-subject text-center'></div>
								<div class='thread-results text-center'></div>

							</div>
						</div>


						<!-- // tab 4 - Final steps with recruit -->


						<div class='tab-pane' id='tab4'>

							<p>Now, you are ready to finalize your new recruit and take care of the paperwork associated with each new recruit. <strong>Be sure to ask</strong> if there are any questions or concerns your recruit may have. You should also remind him/her that <strong>you will be their squad leader</strong> and can come to you if they have any issues in the relative future.</p><p>Perform these final steps to complete the recruitment:</p>

							<fieldset id="checkArray">
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Have them adjust their forum (<a class="popup-link" href="http://www.clanaod.net/forums/sarcoth_profilemod.php">Forum &raquo; Settings &raquo; Edit AOD Member Info</a>) profile settings</label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Request (optional) that they enable email notifications of new private messages on the forums. <button type="button" class="btn btn-info btn-xs" data-toggle="popover" data-content="We PM members after 14 days of forum inactivity. Without this setting, they will not be notified of the new PM.">Why?</button></label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Have them change their name on Teamspeak: <code class='rank-name'>NaN</code><i class='fa fa-copy text-primary player-name-copy copy-button' title='Copy link to clipboard' href='#'></i></label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Accept or invite them into ONE Battlelog platoon, and adjust ingame tags if necessary</label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Give them the "AOD Member" server group permission (Right-click &raquo; Server Groups &raquo; AOD Member) on TeamSpeak</label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Remind them that their login name will be changing in 24/48 hours</label>
								</div>
								<div class="checkbox">
									<label><input type="checkbox" value="" name="chk[]">Introduce them to the other members in the division</label>
								</div>
							</fieldset>
							<div class="margin-top-20">
								<p class="text-muted">Note: All steps must be marked complete in order to continue.</p>
							</div>
						</div>


						<!-- // tab 5 - forum integration actions -->


						<div class='tab-pane' id='tab5'>

							<div class='col-md-12'>

								<div role='tabpanel'>

									<ul class='nav nav-tabs' role='tablist'>
										<li role='presentation' class='active'><a href='#member-request' aria-controls='member-request' role='tab' data-toggle='tab'><span class='badge'>1</span> Request new member status</a></li>
										<li role='presentation'><a href='#division-post' aria-controls='division-post' role='tab' data-toggle='tab'><span class='badge'>2</span> Post to division structure</a></li>
										<li role='presentation'><a href='#welcome-pm' aria-controls='welcome-pm' role='tab' data-toggle='tab'><span class='badge'>3</span> Send Welcome PM</a></li>
										<li role='presentation'><a href='#welcome-post' aria-controls='welcome-post' role='tab' data-toggle='tab'><span class='badge'>4</span> Post Welcome thread</a></li>
									</ul>

									<div class='tab-content'>
										<div role='tabpanel' class='tab-pane' id='division-post'>
											<div class='row margin-top-20'>

												<div class='col-md-6'>
													<p>A division structure post needs to be made so that your new recruit can be added to the forum thread in addition to being tracked here. The box to the right shows what your division structure post should look like, including the information you have provided.</p>
													<p>Click the copy button to copy the contents of the box to your clipboard. Then follow the division structure link to make your post.</p>
													<p class='margin-top-20'><a class='popup-link' href='http://www.clanaod.net/forums/showthread.php?t=<?php echo $division->division_structure_thread ?>' class='text-center'><button type='button' class='btn btn-primary'>Open Division Structure</button></a></p>
												</div>

												<div class='col-md-6'>
													<div class='well code'>
														<button type='button' class='division-code-btn copy-button btn btn-default tool pull-right' title='Copy to clipboard'><i class='fa fa-copy'></i></button> 
														<code class='post-code' data-post="<?php echo RecruitingString::findByName('division-structure-post', 2)->string; ?>"></code>
													</div>
												</div>

											</div>
										</div>

										<div role='tabpanel' class='tab-pane' id='welcome-pm'>
											<div class='row margin-top-20'>
												<div class='col-md-6'>
													<p>In addition to your discussion with your new recruit, it's always a good idea to recap. For this reason, we like to send follow-up PMs to our new members summarizing what we went over in case they have any questions. It's also a good way to start a conversation with them on the forums, and generally a good way to close things up.</p>
													<p>Click the copy button to copy the contents of the box to your clipboard. Then follow the link to send a PM to your recruit.</p>
													<p class='margin-top-20'><a href='http://www.clanaod.net/forums/private.php?do=newpm&u=' class='text-center pm-link'><button type='button' class='btn btn-primary'>Send Forum PM</button></a></p>
												</div>

												<div class='col-md-6'>
													<div class='well code'>
														<button type='button' class='welcome-pm-btn copy-button btn btn-default tool pull-right' title='Copy to clipboard'><i class='fa fa-copy'></i></button> 
														<code class='welcome-code' data-post="<?php echo RecruitingString::findByName('welcome-pm', 2)->string; ?>"></code>
													</div>
												</div>

											</div>
										</div>


										<div role='tabpanel' class='tab-pane active' id='member-request'>
											<div class='row margin-top-20'>
												<div class='col-md-8'>
													<p>A request must be made so your new recruit can be set as an AOD member on the forums, and be able to see all the hidden content specifically for our division.</p>
													<p>You will need to copy the member id to the right, and use it in the form that appears using the button below.</p>
													<p>Keep in mind that you do not have to enter a name into the member status request if their name doesn't need to change. You need only enter the member id.</p>
													<p class='margin-top-20'><a class='popup-link' href='http://www.clanaod.net/forums/misc.php?do=form&fid=39' class='text-center' target='_blank'><button type='button' class='btn btn-primary'>Submit Request</button></a></p>
												</div>
												<div class='col-md-4 well'>
													<p><strong>Forum User ID</strong>:</p>
													<button type='button' class='member-status-btn copy-button btn btn-default tool pull-right' title='Copy to clipboard'><i class='fa fa-copy'></i></button>
													<code class='final_member_id'></code>
												</div>

											</div>
										</div>

										<div role='tabpanel' class='tab-pane' id='welcome-post'>
											<div class='row margin-top-20'>
												<div class='col-md-12'>
													<p>A welcome thread is created for each new recruit. It serves multiple purposes: It makes the recruit feel welcome. This is very important., It gives everyone a chance to know who is new in the division, which includes people from other divisions and even the leadership. and lastly, if you do nothing else on our forums, say hi to the new members.</p>

													<p>It also wouldn't hurt to let your new recruit know you made a new post for them, so they can introduce themselves to everyone.</p>

													<p class='margin-top-20'><a class='popup-link' href='http://www.clanaod.net/forums/newthread.php?do=newthread&f=<?php echo $division->welcome_forum ?>' class='text-center'><button type='button' class='btn btn-primary'>Create welcome thread</button></a></p>
												</div>

											</div>
										</div>



									</div>
								</div>
							</div>
						</div>


						<!-- // tab 6 - completion -->


						<div class='tab-pane' id='tab6'>
							<p>If you have successfully completed all the required previous steps of the recruitment process, you are now ready to add the new recruit to the division. Click the button below to submit the new member's information.</p>
							<p>Keep in mind that new members will appear as "pending" until their membership status in AOD is approved by a MSgt or above. This generally takes around 24 hours or less. New members will also not have any recent server actvity information. After a few days, this information will have had time to update in the tracker.</p>
							<p class="text-center margin-top-50">
								<button class="btn btn-success btn-lg" id="storePlayer"><i class="fa fa-user-plus fa-lg"></i> Add member to division</button>
							</p>
						</div>

					</div>	
				</form>
			</div>


			<div class='panel-footer'>
				<ul class='pager wizard'>
					<li class='btn btn-default previous first' style='display:none;'>First</li>
					<li class='btn btn-primary pull-left previous'> <i class="fa fa-arrow-left"></i> Previous</li>
					<li class='btn btn-default next last' style='display:none;'>Last</li>

					<li class='btn btn-primary pull-right next'>Continue <i class="fa fa-arrow-right"></i></li>

				</ul>
			</div>

		</div><!-- end panel -->
	</div><!-- end root wizard -->
</div><!-- end container -->