<html>
<head>

	<title>AOD | Division Tracker v2</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<base href="<?php echo Flight::get('base_url') ?>">

	<!--[if IE]>
	<script type="text/javascript">
    // Fix for IE ignoring relative base tags.
    (function() {
        var baseTag = document.getElementsByTagName('base')[0];
        baseTag.href = baseTag.href;
    })();
	</script>
	<![endif]-->

	<link rel="Shortcut Icon" href="favicon.ico" type="image/x-icon" />
	<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Source+Sans+Pro'>
	<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/lumen/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/jquery.powertip.min.css">
	<link rel="stylesheet" href="assets/css/font-awesome.min.css">
	<link href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css" rel="stylesheet">
	<link href="assets/css/jquery.dataTables.css" rel="stylesheet">
	<link href="assets/css/dataTables.tableTools.css" rel="stylesheet">
	<link href="assets/css/sweetalert2.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">

</head>
<body>

	<div class="modal viewPanel fade">
		<div class="modal-dialog modal-lg">
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
					<a class="navbar-brand" href="./"><img src='assets/images/logo.svg' class='pull-left'  /> <span class="hidden-sm hidden-xs"><strong class='logo'>AOD</strong> <small>Division Tracker <sup>v2</sup></small></span></a>
				</div>

				<?php if (User::isLoggedIn()): ?>
					<div class="navbar-collapse collapse">
						<form class="navbar-form navbar-right" role="search">
							<div class="form-group has-feedback">
								<input type='text' class='form-control' id='member-search' placeholder='Search for a player...' />
								<span id="searchclear" class="fa fa-times-circle fa-2x text-muted"></span>
								<div id='member-search-results' class='scroll'></div>
							</div>
						</form>

						<ul class="nav navbar-nav navbar-left">
							<?php if ($user->role >= 1) : ?>
								<li class="dropdown">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#">Activity <span class="caret"></span></a>
									<div class="popup dropdown-menu">
										<ul class="activity">
											<?php foreach(UserAction::find_all() as $action) : ?>
												<li>
													<i class="<?php echo $action->icon; ?> fa-2x"></i>
													<div>
														<?php echo UserAction::humanize($action->type_id, $action->target_id, $action->user_id, $action->verbage); ?>
														<span><?php echo formatTime(strtotime($action->date)); ?></span>
													</div>
												</li>
											<?php endforeach; ?>
										</ul>
									</div>
								</li>
							<?php endif; ?>

							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">User CP<span class="caret"></span></a>

								<ul class="dropdown-menu" role="menu">
									<li><a href="member/<?php echo $member->member_id; ?>"><?php echo ucwords($member->forum_name); ?><span class="pull-right"><?php echo Member::avatar($member->member_id) ?></span></a></li>
									<li class="divider"></li>
									<li class='disabled'><a href='#' disabled>Role: <?php echo getUserRoleName($user->role); ?></a></li><li class='divider'></li>
									<li><a href="#" data-toggle="pill" class="settings-btn"> Settings</a></li>
									<li><a href="http://www.clanaod.net/forums/member.php?u=<?php echo $member->member_id; ?>" target="_blank"> Forum profile</a></li>
									<li> <a href="help/" role="button">Help</a> </li>
									<li class="divider"></li>
									<li><a href="#" data-toggle="pill" class="logout-btn"><i class="fa fa-lock pull-right"></i> Logout</a></li>
								</ul>
							</li>

							<!-- showing tools if squad leader or above -->
							<?php if ($user->role > 0): ?>

								<!-- user tools -->
								<li class="dropdown multi-level">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools<span class="caret"></span></a>
									<ul class="dropdown-menu" role="menu">
										<?php if ($user->role > 2 || User::isDev()) : ?>
											<li class="dropdown-submenu">
												<a href="#" class="dropdown-toggle" data-toggle="dropdown">Reports</a>
												<ul class="dropdown-menu">
													<li><a href="#">Report</a></li>
												</ul>
											</li>
											<li class='divider'></li>
										<?php endif; ?>
										<?php foreach ($tools as $tool) : ?>
											<?php $disabled = ($tool->disabled) ? "disabled" : null; ?>
											<li><a href="<?php echo $tool->tool_path ?>" class="<?php echo $tool->class . " " . $disabled ?>"><?php echo $tool->tool_name ?></a></li>
										<?php endforeach; ?>
									</ul>
								</li>
							<?php endif; ?>

							<!-- supported divisions -->
							<li class="dropdown multi-level">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Divisions <span class="caret"></span></a>


								<ul class="dropdown-menu" role="menu">
									<?php foreach ($divisions as $division) : ?>
										<?php $platoons = Platoon::find_all($division->id); ?>
										<li class="dropdown-submenu"><a href='divisions/<?php echo $division->short_name ?>'><?php echo $division->full_name ?></a>
											<?php if ((array) count($platoons)): ?>
												<ul class="dropdown-menu">
													<?php foreach ($platoons as $platoonLink) : ?>
														<li><a href="divisions/<?php echo $division->short_name ?>/platoon/<?php echo $platoonLink->number ?>"><?php echo $platoonLink->name; ?></a></li>
													<?php endforeach; ?>
												</ul>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</li>


							<!-- bug reports -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Bug Reports<span class="caret"></span></a>
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

		<?php if(User::isLoggedIn()): ?>
			<?php $alerts = Alert::find_all($_SESSION['userid']); ?>
			<?php if (count((array) $alerts)) : ?>
				<div class="container margin-top-20">
					<?php foreach($alerts as $alert) : ?>
						<div data-id="<?php echo $alert->id; ?>" data-user="<?php echo $user->id; ?>" class="alert-dismissable alert alert-<?php echo $alert->type; ?>" role="alert">
							<button type="button" class="close" data-dismiss="alert">
								<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
							</button>
							<?php echo $alert->content; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<!-- content -->
		<?php echo $content; ?>

		<div class="margin-top-50"></div>
		<div id="push"></div>
	</div>

	<div id="footer" class="navbar navbar-default">
		<div class="container">
			<small class="text-muted">Copright &copy; Angels of Death <span class="hidden-xs">2005-2015. All rights reserved.</span><br /><span class="hidden-xs"> Built to run on <a href="https://www.google.com/chrome/"><strong>Google Chrome</strong></a></span></small>
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
	<script src="assets/js/libraries/bootstrap-multiselect.js"></script>
	<script src="assets/js/libraries/ZeroClipboard.js"></script>
	<script src="assets/js/libraries/touchpunch.js"></script>
	<script src="assets/js/libraries/sweetalert2.min.js"></script>
	<script src="assets/js/libraries/chartjs/Chart.Core.js"></script>
	<script src="assets/js/libraries/chartjs/Chart.Doughnut.js"></script>
	<script src="assets/js/main.js"></script>

	<?php if (isset($js) && file_exists("assets/js/{$js}.js")) :?>
		<script src="assets/js/<?php echo $js ?>.js"></script>
	<?php endif; ?>

	<?php

	// debug information

	if ( isset($_SESSION['userid']) ) {
		Flight::aod()->show_sql = true;
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
	}

	?>

</body>
</html>












