<div class='container'>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Reports</li>
		<li class='active'>Retention numbers</li>
	</ul>

	<div class='page-header'>
		<h2><strong>Reports <small>Retention Numbers</small></strong></h2>
	</div>
	<p>This data is based on direct interaction with the tracker. Only recruiting or removals done through the tracker will be available for view here.</p>
	<hr />

	<?php if (count($recruited)): ?>

		<?php // for copy area ?>
		<?php $activity_bb = "[table=\"width: 500\"][tr][td][b]Member[/b][/td][td][b]Number recruited[/b][/td][/tr]"; ?>



		<div class="row">
			<div class="col-md-12">
				<div class='panel panel-primary'>
					<div class='panel-heading'>Recruiting By Week</div>
					<div class='panel-body'>

						<?php $data = array(); ?>
						<?php $labels = array(); ?>
						<?php // echo json_encode($monthlyBreakdown);die; ?>

						<?php foreach ($monthlyBreakdown as $day) { array_push($labels, date('m-d', strtotime($day['week_beginning']))); } ?>
						<?php $data['labels'] = $labels; ?>

						<?php $datastats = array(); ?>
						<?php foreach ($monthlyBreakdown as $day) { array_push($datastats, $day['count']);  } ?>

						<?php $data['datasets'] = [
						[
						'label' => "Recruited",
						'fillColor' => "rgba(220,220,220,0.2)",
						'strokeColor' => "rgba(220,220,220,1)",
						'pointColor' => "rgba(220,220,220,1)",
						'pointStrokeColor' => "#28b62c",
						'pointHighlightFill' => "#fff",
						'pointHighlightStroke' => "rgba(220,220,220,1)",
						'data' => $datastats
						]
						];
						$data = json_encode($data);
						?>

						<div id="canvas3" data-stats="<?php echo htmlentities($data, ENT_QUOTES, 'UTF-8'); ?>">
							<canvas id="chart3" style="width:100%; height: 200px;"/>
						</div>
					</div>
					<div class="panel-footer text-muted"><small>Reflects activity over the past 30 days</small></div>
				</div>
			</div>
		</div>


		<div class="col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">Recruiting Tallies</div>

				<?php $i = 0; ?>
				<?php foreach ($recruited as $member): ?>
					<a href="member/<?php echo $member['member_id'] ?>" class="list-group-item"><?php echo ucfirst($member['forum_name']) ?><span class="pull-right"><?php echo $member['recruited'] ?></span></a>
					<?php $activity_bb .= "[tr][td]" . ucfirst($member['forum_name']) . "[/td][td]" . $member['recruited']. "[/td][/tr]"; ?>
					<?php $i = $i + $member['recruited']; ?>
				<?php endforeach; ?>
				<?php $activity_bb .= "[/table]"; ?>


				<div class="panel-footer text-muted"><strong>Total</strong><span class="pull-right"><?php echo $i ?></span></div>
			</div>
		</div>


		<div class="col-md-6">
			<div class='panel panel-primary'>
				<div class='panel-heading'>Monthly Breakdown</div>
				<div class='panel-body'>

					<?php $data = array(); ?>
					<?php $labels = array(); ?>

					<?php foreach ($byTheMonth as $day) { array_push($labels, date('F', strtotime($day['month_beginning']))); } ?>
					<?php $data['labels'] = $labels; ?>

					<?php $datastats = array(); ?>
					<?php foreach ($byTheMonth as $day) { array_push($datastats, $day['count']);  } ?>

					<?php $data['datasets'] = [
					[
					'label' => "Recruited",
					'fillColor' => "rgba(220,220,220,0.2)",
					'strokeColor' => "rgba(220,220,220,1)",
					'pointColor' => "rgba(220,220,220,1)",
					'pointStrokeColor' => "#28b62c",
					'pointHighlightFill' => "#fff",
					'pointHighlightStroke' => "rgba(220,220,220,1)",
					'data' => $datastats
					]
					];
					$data = json_encode($data);
					?>

					<div id="canvas2" data-stats="<?php echo htmlentities($data, ENT_QUOTES, 'UTF-8'); ?>">
						<canvas id="chart2" style="width:100%; height: 200px;"/>
					</div>
				</div>
				<div class="panel-footer text-muted"><small>Reflects activity over the past 5 months</small></div>
			</div>
		</div>



		<div class="row">
			<div class="col-xs-12">
				<div class="panel panel-default">
					<div class="panel-heading">Share data</div>
					<pre class='well code' id='activity' onClick="$(this).selectText()"><?php echo $activity_bb; ?></pre>
				</div>
			</div>
		</div>

	<?php else: ?>

		<p>No data available.</p>

	<?php endif; ?>






</div>
