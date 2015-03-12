<html>
<head>

	<title>AOD | Division Tracker v2</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<base href="<?php echo Flight::get('base_url') ?>">
	<link rel="Shortcut Icon" href="assets/images/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/lumen/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/jquery.powertip.min.css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">	
	<link href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css" rel="stylesheet">	
	<link href="assets/css/jquery.dataTables.css" rel="stylesheet">
	<link href="assets/css/dataTables.tableTools.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">

</head>
<body>

	<!-- modal for ajax dialogs -->
	<div class="modal viewPanel fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="viewer fadeIn animate"></div>
			</div>
		</div>
	</div>

	<div id="wrap">
		<div class="push-top"></div>

		<div class="navbar navbar-default navbar-nav navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="./"><img src='assets/images/logo.svg' class='pull-left'  /> <strong class='logo'>AOD</strong> <small>Division Tracker <small>v2</small></small></a>
				</div>

				<?php if (User::isLoggedIn()): ?>

					<div class="navbar-collapse collapse">

						<form class="navbar-form navbar-right" role="search">
							<div class="form-group">
								<input type='text' class='form-control' id='member-search' placeholder='Search for a player...' />
								<div id='member-search-results' class='scroll'></div> 
							</div>
						</form>

						<ul class="nav navbar-nav navbar-left">
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">User CP<span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="member/<?php echo $_SESSION['userid']; ?>"><?php echo ucwords($_SESSION['username']); ?><span class="pull-right"><?php echo Member::avatar($_SESSION['userid']) ?></span></a></li>
									<li class="divider"></li>
									<li><a href="#" data-toggle="pill" class="settings-btn"> Settings</a></li>
									<li><a href="http://www.clanaod.net/forums/member.php?u=<?php echo $_SESSION['userid']; ?>" target="_blank"> Forum profile</a></li>
									<li> <a href="/help" role="button">Help</a> </li>
									<li class="divider"></li>
									<li><a href="#" data-toggle="pill" class="logout-btn"><i class="fa fa-lock pull-right"></i> Logout</a></li>
								</ul>
							</li>						


							<!-- showing tools if squad leader or above -->
							<?php if ($user->role > 0): $roleName = getUserRoleName($user->role); ?>

								<!-- user tools -->
								<li class="dropdown">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools<span class="caret"></span></a>
									<ul class="dropdown-menu" role="menu">
										<li class='disabled'><a href='#' disabled><?php echo $roleName ?></a></li><li class='divider'></li>
										<?php foreach ($tools as $tool) : ?>
											<li><a href="<?php echo $tool->tool_path ?>" class="<?php echo $tool->class ?>"><?php echo $tool->tool_name ?></a></li>
										<?php endforeach; ?>
									</ul>
								</li>

							<?php endif; ?>


							<!-- supported divisions -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Divisions <span class="caret"></span></a>

								
								<ul class="dropdown-menu" role="menu">
									<?php foreach ($divisions as $division) : ?>

										<li><a href='/divisions/<?php echo $division->short_name ?>'><img src='assets/images/game_icons/tiny/<?php echo $division->short_name ?>.png' class='pull-right' /><?php echo $division->full_name ?></a></li>

									<?php endforeach; ?>
								</ul>

							</li>


							<!-- notifications -->
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<span class="count">100+</span> Notifications <span class="caret"></span>
								</a>
								<div class="popup dropdown-menu">
									<ul class="activity">
										<li>
											<i class="fa fa-clock-o fa-2x text-danger"></i>
											<div>You have <a href="#">3 recruits</a> awaiting promotion!
												<span>14 minutes ago</span>
											</div>
										</li>
										<li>
											<i class="fa fa-angle-double-up fa-2x text-success"></i>
											<div>
												<a href="#">CupOHemlock</a> promoted <a href="#">GinaLou</a> to Master Super General
												<span>14 minutes ago</span>
											</div>
										</li>
										<li>
											<i class="fa fa-user fa-2x text-success"></i>
											<div><a href="#">31drew31</a> added <a href="#">Rct Jonesgirl</a> to <a href="#">Platoon 1</a>
												<span>About 2 hours ago</span>
											</div>
										</li>
										<li>
											<i class="fa fa-comment text-primary fa-2x"></i>
											<div>
												<a href="#">Redguard</a> posted a <a href="#">comment</a> on Platoon 2's <a href="#">notes</a>
												<span>5 minutes ago</span>
											</div>
										</li>

										<li>
											<i class="fa fa-flag fa-2x text-danger"></i>
											<div><a href="#">Guybrush</a> removed <a href="#">JoeSchmoe</a> from <a href="#">Platoon 2</a>
												<span>About 7 hours ago</span>
											</div>
										</li>

										<li>
											<i class="fa fa-angle-double-up fa-2x text-success"></i>
											<div>
												<a href="#">CupOHemlock</a> promoted <a href="#">GinaLou</a> to Master Super General
												<span>14 minutes ago</span>
											</div>
										</li>
										<li>
											<i class="fa fa-comment text-primary fa-2x"></i>
											<div>
												<a href="#">Redguard</a> posted a <a href="#">comment</a> on Platoon 2's <a href="#">discussion feed</a>
												<span>35 minutes ago</span>
											</div>
										</li>
										<li>
											<i class="fa fa-flag fa-2x text-danger"></i>
											<div><a href="#">Guybrush</a> removed <a href="#">JoeSchmoe</a> from <a href="#">Platoon 2</a>
												<span>About 2 hours ago</span>
											</div>
										</li>
									</ul>
								</div>
							</li>
						</ul>
					</div>


				<?php else: ?>

					<div class="navbar-collapse collapse">
						<ul class="nav navbar-nav navbar-right">
							<li class="navbar-text text-muted">Not logged in</li>
						</ul>
					</div>

				<?php endif; ?>		

				<div class='container row'  style='position: absolute; margin-top: 10px;'>
					<div class='alert-box'></div>
				</div>
			</div>
		</div>

		<!-- content -->
		<?php echo $content; ?>

		<div class="margin-top-50"></div>
		<div id="push"></div>
	</div>

	<div id="footer" class="navbar navbar-default">
		<div class="container">
			<small class="text-muted col-xs-6">Copright &copy; Angels of Death <span class="hidden-xs">2005-2015. All rights reserved.</span><br /><span class="hidden-xs"> Built to run on <a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a></span></small>
			<small class="text-muted col-xs-6 text-right userList"><img src="https://aod.sitespot.com/public/images/loading_2.gif" style="background: transparent;" /> Loading users...</small>
		</div>
	</div>

	<script src="assets/js/libraries/jquery-2.1.1.min.js"></script>
	<script src="assets/js/libraries/jquery-ui.min.js"></script>
	<script src="assets/js/libraries/jquery.easing.min.js"></script>
	<script src="assets/js/libraries/jquery.powertip.min.js"></script>
	<script src="assets/js/libraries/bootstrap.min.js"></script>
	<script src="assets/js/libraries/jquery.dataTables.min.js"></script>
	<script src="assets/js/libraries/dataTables.bootstrap.js"></script>
	<script src="assets/js/libraries/dataTables.tableTools.min.js"></script>
	<script src="assets/js/libraries/jquery.bootstrap.wizard.min.js"></script>
	<script src="assets/js/libraries/ZeroClipboard.js"></script>
	<script src="assets/js/main.js"></script>

</body>
</html>












