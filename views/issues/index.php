<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'><?php echo ucwords($filter); ?> Issues</li>
	</ul>

	<div class='page-header'>
		<h2>
			<strong>Issue Tracker</strong>
			<div class="btn-group pull-right">
				<a class="btn btn-success create-issue" href="#"><i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm">Create Report</span></a>

			</div>
		</h2>
	</div>

	<div class="row">
		<div class="col-md-8">
			<?php echo $issuesList ?>
		</div>

		<div class="col-md-4">
			<?php echo $filters ?>
		</div>

	</div>

</div>

