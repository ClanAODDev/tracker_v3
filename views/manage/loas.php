<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Manage Leaves of Absence</li>
	</ul>


	<div class='page-header'>
		<h1><strong>Manage</strong> <small>Leaves of Absence</small><img class='pull-right' src='assets/images/game_icons/large/<?php echo $division->short_name ?>.png'/></h1>
	</div>

	<?php if (LeaveOfAbsence::count_expired($division->id) > 0) : ?>
		<div class='alert alert-info'><p><i class='fa fa-exclamation-triangle'></i> Your division has <?php echo LeaveOfAbsence::count_expired($division->id); ?> expired leaves of absence which need to be handled.</p></div>
	<?php endif; ?>

	<?php if (LeaveOfAbsence::count_pending($division->id) && $user->role >= 1)  : ?>

		<div class='panel panel-primary margin-top-20' id='pending-loas'>
			<div class='panel-heading'>Pending Leaves of Absence</div>
			<table class='table table-striped table-hover' id='ploas'>
				<thead>
					<tr>
						<th>Member name</th>
						<th>Reason</th>
						<th>End Date</th>
						<th class='text-center'>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php $pendingLoas = LeaveOfAbsence::find_pending($division->id); ?>
					
					<?php foreach ($pendingLoas as $player) : ?>
						<tr data-id='<?php echo $player->member_id ?>' data-comment='<?php echo $player->comment ?>'>
							<td><?php echo Member::findForumName($player->member_id); ?></td> 
							<td><?php echo $player->reason; ?></td>
							<td><?php echo date("M d, Y", strtotime($player->date_end)); ?></td>
							<td class='text-center' style='vertical-align: middle;'><h4><span class='label bg-warning'><i class='fa fa-clock-o' title='Pending'></i> Pending</span></h4></td>

							<?php if ($user->role >= 1) : ?>
								<td class='text-center loa-actions' style='opacity: .2;'><button class='btn btn-default btn-block view-pending-loa' title='Review LOA'>Review LOA</button></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<div class='alert hide loa-alerts'></div>
	<div class='panel panel-primary margin-top-20' id='active-loas'>
		<div class='panel-heading'>Approved Leaves of Absence</div>
		<table class='table table-striped table-hover' id='loas'>
			<thead>
				<tr>
					<th>Member name</th>
					<th>Reason</th>
					<th>End Date</th>
					<th class='text-center'>Status</th>
				</tr>
			</thead>
			<tbody>

				<?php if (count(LeaveOfAbsence::count_active($division->id))) : ?>
					<?php foreach (LeaveOfAbsence::findAll($division->id) as $player) : ?>


						<?php $expired = ( strtotime(date("M d, Y", strtotime($player->date_end))) < strtotime('now')) ? true : false; ?>
						<?php $comment = (!empty($player->comment)) ? htmlentities($player->comment, ENT_QUOTES) : "Not available"; ?>
						<?php $date_end = date("M d, Y", strtotime($player->date_end)); ?>
						<?php $approved_by = (!empty($player->approved_by)) ? Member::findForumName($player->approved_by) : "Not available"; ?>

						<tr data-id='<?php echo $player->member_id ?>' data-approval='<?php echo $approved_by ?>' data-comment='<?php echo $comment ?>'>
							<td><?php echo Member::findForumName($player->member_id); ?></td> 
							<td><?php echo $player->reason ?></td>
							<td>
								<?php if ($expired) : ?><span class='text-danger' title='Expired'><?php echo $date_end ?></span>
								<?php else : echo $date_end ?>
								<?php endif; ?>
							</td>
							<td class='text-center' style='vertical-align: middle;'>
								<?php if ($expired) : ?><h4><span class='label bg-danger'><i class='fa fa-times-circle' title='Expired'></i> Expired</span></h4> 
								<?php else : ?><h4><span class='label bg-success'><i class='fa fa-check' title='Active'></i> Active</span></h4> 
								<?php endif; ?>
							</td>

							<?php if ($user->role >= 1) : ?>
								<td class='text-center loa-actions' style='opacity: .2;'><button class='btn btn-default btn-block view-active-loa' title='Review LOA'>Review LOA</button></td>
							<?php endif; ?>
						</tr>

					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class='panel panel-default margin-top-20'>

		<div class='panel-heading'>Add New Leave of Absence</div>
		<table class='table'>
			<tbody>
				<tr>
					<form id='loa-update' action='#'>
						<td><input type='text' class='form-control' name='id' placeholder='Member id' required></input></td>
						<td><input type='text' class='form-control' name='date' id='datepicker' placeholder='mm/dd/yyyy' required></input></td>
						<td><select class='form-control' name='reason' required><option>Military</option><option>School</option><option>Work</option><option>Medical</option><option>Personal</option></select></td>
						<td class='text-center'><button class='btn btn-block btn-success' type='submit'>ADD LOA</button></td>
					</form>
				</tr>
			</tbody>
		</table>
		<link href='assets/css/jquery-ui-smooth.css' rel='stylesheet'>
	</div>
</div>





