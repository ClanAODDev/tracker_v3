<div class="container">
	
	<ul class="breadcrumb">
		<li><a href="./">Home</a></li>
		<li><a href="divisions/<?php echo $division->short_name ?>"><?php echo $division->full_name ?></a></li>
		<li class="active"><?php echo $platoon->name ?></li>
	</ul>

	<div class="row page-header">

		<div class="col-xs-7 platoon-name">
			<h2><img src="assets/images/game_icons/48x48/<?php echo $division->short_name ?>.png" /> <strong><?php echo $platoon->name ?></strong> <small class="platoon-number"><?php echo ordSuffix($plt); ?> <?php echo Locality::run('Platoon', $division->id); ?></small></h2>
		</div>

		<div class="col-xs-5">
			<?php if ($user->role >= 2 || User::isDev()) : ?>

				<div class="btn-group pull-right">
					<button type="button" class="btn btn-default disabled"><i class="fa fa-pencil"></i> Edit</button>
					<?php if ($member->platoon_id == $platoon->id || $user->role > 2 || User::isDev()): ?>
						<a class="btn btn-default" href="divisions/<?php echo $division->short_name ?>/platoon/<?php echo $platoon->number ?>/manage"><i class="fa fa-users"></i> Manage</a>
					<?php endif; ?>
					<a class="btn btn-default send-pm disabled" data-members="<?php echo ($memberIdList) ? implode(",", $memberIdList) : NULL; ?>" href="<?php echo PRIVMSG; ?>" target="_blank"><i class="fa fa-comment"></i> Send Platoon PM</a>
				</div>

			<?php endif; ?>
		</div>

	</div>

	<div class="row">

		<div class="col-md-8">			
			<?php echo $membersTable; ?>
		</div>

		<div class="col-md-4 hidden-xs">
			<?php echo $statistics; ?>
		</div>

	</div>
	
</div>
