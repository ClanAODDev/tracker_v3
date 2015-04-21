<?php

$game_info = get_game_info($user_game);
$game_icon = strtolower($game_info['short_name']);
$game_icon = "<img class='pull-right' src='/public/images/game_icons/large/{$game_icon}.png'/>";



// revoke power?
$revokeBtn = NULL;
$approveBtn = NULL;
$contactBtn = NULL;
$ploaTable = NULL;
$loaList = NULL;

// fetch leaves of absence
$appLoas = get_approved_loas($user_game);
$pendLoas = get_pending_loas($user_game);

if ($userRole >= 1) {
	$pendingActions = "<td class='text-center loa-actions' style='opacity: .2;'><button class='btn btn-default btn-block view-pending-loa' title='Review LOA'>Review LOA</button></td>";
	$activeActions = "<td class='text-center loa-actions' style='opacity: .2;'><button class='btn btn-default btn-block view-active-loa' title='Review LOA'>Review LOA</button></td>";
}


?>


<div class='container fade-in'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Manage Leaves of Absence</li>
	</ul>

	<div class='page-header'>
		<h1><strong>Manage</strong> <small>Leaves of Absence</small>{$game_icon}</h1>
	</div>


	<!-- 	// count expired
		$obligAlerts = NULL;
		$loa_expired = count_expired_loas($user_game);
	-->
	<?php if ($loa_expired > 0) : ?>
		$obligAlerts = "<div class='alert alert-info'><p><i class='fa fa-exclamation-triangle'></i> Your division has ({$loa_expired}) expired leaves of absence which need to be handled.</p></div>";
	<?php endif; ?>


	<!-- // pending loas
	if ($userRole >= 1) {
	// do we have any pending leaves of absence? -->

	<tr data-id='{$member['member_id']}' data-comment='{$comment}'>
		<td>{$member['forum_name']}</td> 
		<td>{$member['reason']}</td>
		<td>{$date_end}</td>
		<td class='text-center' style='vertical-align: middle;'>{$status_icon}</td>
		{$pendingActions}
	</tr>

	<?php if (count($pendLoas)) : ?>


		<?php foreach ($pendLoas as $member) : ?>
			$date_end = date("M d, Y", strtotime($member['date_end']));
			$comment = htmlentities($member['comment'], ENT_QUOTES);
			$expired = ( strtotime($date_end) < strtotime('now')) ? true : false;
			$status_icon =  "<h4><span class='label bg-warning'><i class='fa fa-clock-o' title='Pending'></i> Pending</span></h4>";




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
						{$ploaList}
					</tbody>
				</table>
			</div>";
		<?php endforeach; ?>
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

				// do we have any active leaves of absence?
				<?php if (count($appLoas)) : ?>
					<?php foreach ($appLoas as $member) : ?>
						$date_end = date("M d, Y", strtotime($member['date_end']));
						$expired = ( strtotime($date_end) < strtotime('now')) ? true : false;
						$comment = (!empty($member['comment'])) ? htmlentities($member['comment'], ENT_QUOTES) : "Not available";
						$date_end = ($expired) ? "<span class='text-danger' title='Expired'>{$date_end}</span>" : $date_end;
						$approved_by = (!empty($member['approved_by'])) ? get_forum_name($member['approved_by']) : "Not available";
						$status_icon = ($expired) ? "<h4><span class='label bg-danger'><i class='fa fa-times-circle' title='Expired'></i> Expired</span></h4>" : "<h4><span class='label bg-success'><i class='fa fa-check' title='Active'></i> Active</span></h4>";

						<tr data-id='{$member['member_id']}' data-approval='{$approved_by}' data-comment='{$comment}'>
							<td>{$member['forum_name']}</td> 
							<td>{$member['reason']}</td>
							<td>{$date_end}</td>
							<td class='text-center' style='vertical-align: middle;'>{$status_icon}</td>
							{$activeActions}
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
		<link href='/public/css/jquery-ui-smooth.css' rel='stylesheet'>
	</div>
</div>





