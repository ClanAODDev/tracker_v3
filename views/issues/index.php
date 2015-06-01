<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Open Issues</li>
	</ul>

	<div class='page-header'>
		<h2>
			<strong>Issue Tracker</strong> <small>Open Issues</small>
			<div class="btn-group pull-right">
				<a class="btn btn-success create-issue" href="#"><i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm">Create Report</span></a>

			</div>
		</h2>
	</div>

	<?php if (count($open_issues)): ?>
		<?php foreach($open_issues as $issue): ?>
			<div class="panel panel-default">

				<div class="panel-heading">
					<a href="./issues/<?php echo $issue->getNumber(); ?>"><strong><?php echo ucwords($issue->getTitle()); ?></strong></a>
					<?php if ($issue->getComments()): ?>
						<small class="comment-count text-muted"> &mdash; <i class="fa fa-comment"></i> <?php echo $issue->getComments() ?></small>
					<?php endif; ?>
					<small class="pull-right text-muted"> Lat updated <?php echo formatTime(strtotime($issue->getUpdatedAt())); ?></small>
				</div>

				<div class="panel-body">
					<?php $body = $issue->getBody(); ?>
					<small class="text-muted"><?php echo (!empty($body)) ? excerpt($body, 30) : "No description"; ?></small>
				</div>

			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="clear" style="height: 25px;"></div>
</div>