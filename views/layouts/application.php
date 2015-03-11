<html>
<head>

	<title>AOD | <?php echo APP_TITLE; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="Shortcut Icon" href="/public/images/favicon.ico" type="image/x-icon" />

	
	<script src="/public/js/libraries/jquery-2.1.1.min.js"></script>
	<script src="/public/js/libraries/jquery-ui.min.js"></script>
	<script src="/public/js/libraries/jquery.easing.min.js"></script>
	<script src="/public/js/libraries/jquery.powertip.min.js"></script>

	<script src="/public/js/libraries/bootstrap.min.js"></script>
	<script src="/public/js/libraries/jquery.dataTables.min.js"></script>
	<script src="/public/js/libraries/dataTables.bootstrap.js"></script>
	<script src="/public/js/libraries/dataTables.tableTools.min.js"></script>
	<script src="/public/js/libraries/jquery.bootstrap.wizard.min.js"></script>

	<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/lumen/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/public/css/jquery.powertip.min.css">
	<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">	
	<link href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css" rel="stylesheet">	
	<link href="/public/css/jquery.dataTables.min.css" rel="stylesheet">
	<link href="/public/css/dataTables.tableTools.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="/public/css/style.css">

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
					<a class="navbar-brand" href="/"><img src='/public/images/logo.svg' class='pull-left'  /> <strong class='logo'>AOD</strong> <small><?php echo APP_TITLE; ?></small></a>
				</div>

				<?php if (Flight::) { ?>

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
								<li><a href="/member/<?php echo $forumId; ?>"><?php echo $curUser ?><span class="pull-right"><?php echo $avatar; ?></span></a></li>
								<li class="divider"></li>
								<li><a href="#" data-toggle="pill" class="settings-btn"> Settings</a></li>
								<li><a href="http://www.clanaod.net/forums/member.php?u=<?php echo $forumId; ?>" target="_blank"> Forum profile</a></li>
								<li> <a href="/help" role="button">Help</a> </li>
								<li class="divider"></li>
								<li><a href="#" data-toggle="pill" class="logout-btn"><i class="fa fa-lock pull-right"></i> Logout</a></li>
							</ul>
						</li>						

						<?php if ($userRole > 0) { ?>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools<span class="caret"></span></a>

							<ul class="dropdown-menu" role="menu">
								<?php 
								$roleName = getUserRoleName($userRole);
								echo "<li class='disabled'><a href='#' disabled>{$roleName}</a></li><li class='divider'></li>";
								foreach (build_user_tools($userRole) as $tool) { 
									echo "<li><a href='{$tool['link']}' class='{$tool['class']}'>{$tool['title']}</a></li>";
								} ?>
							</ul>
						</li>
						<?php } ?>

						<!-- divisions -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Divisions <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<?php echo $game_list ?>								
							</ul>
						</li>


						<!-- notifications menu -->

						<!--
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
						</li> -->
						<!-- end notifications menu -->

					</ul>
				</div><!--/.nav-collapse -->

				<?php } else { ?>

				<div class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
						<li class="navbar-text text-muted">Not logged in</li>
					</ul>
				</div>

				<?php } ?>		
				<div class='container row'  style='position: absolute; margin-top: 10px;'>
					<div class='alert-box'></div>
				</div>
			</div>
		</div>