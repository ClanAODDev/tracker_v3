<div class='container'>

	<input type="hidden" id="member_id" value="<?php echo $member->member_id ?>"></input>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Manage part time players</li>
	</ul>


	<div class='page-header'>
		<h2><strong>Manage <small>Part Time Members</small></strong></h2>
	</div>

	<div class="row">
		<div class="col-md-8">
			<div class="panel panel-default">
				<div class="panel-heading">Currently Assigned</div>
				<?php if (count($part_time)): ?>
					<?php foreach($part_time as $member): ?>
						<li class="list-group-item"><?php echo $member->forum_name ?></li>
					<?php endforeach; ?>
				<?php else: ?>
					<p>You have no part time members assigned.</p>
				<?php endif; ?>
			</div><!-- panel -->
		</div><!-- col-md-8 -->


		<div class="col-md-4">
			<div class="panel panel-primary">
				<div class="panel-heading">Add member</div>
				<div class="panel-body">
					<form action="/Division-Tracker/do/add-parttime" method="POST">
						<div class="form-group">
							<label for="member_id" class="control-label">Member ID</label>
							<input type="number" class="form-control" name="member_id" />
						</div>

						<div class="form-group">
							<label for="name" class="control-label">Forum Name</label>
							<input type="text" class="form-control" name="name" />
						</div>

						<div class="form-group">
							<label for="ingame_alias" class="control-label">Ingame Alias</label>
							<input type="text" class="form-control" name="ingame_alias" />
						</div>

						<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add</button>
					</form>
				</div><!-- body -->
			</div><!-- panel -->
		</div><!-- col-md-4 -->


	</div><!-- row -->

</div>
