<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Closed Issues</li>
	</ul>

	<div class='page-header'>
		<h2>
			<strong>Issue Tracker</strong> <small>Closed Issues</small>
		</h2>
	</div>

	<?php if (count($closed_issues)): ?>
		<?php foreach($closed_issues as $issue): ?>
			<div class="panel panel-default">

				<div class="panel-heading">
					<a href="./issues/<?php echo $issue->getNumber(); ?>"><strong><?php echo ucwords($issue->getTitle()); ?></strong></a> &mdash;
					<small class="comment-count"><i class="fa fa-comment text-muted"></i> <?php echo $issue->getComments() ?></small>
					<small class="pull-right text-muted"> Lat updated <?php echo formatTime(strtotime($issue->getUpdatedAt())); ?></small>
				</div>

				<?php if (!empty($issue->getBody())): ?>
					<div class="panel-body">
						<small class="text-muted"><?php echo excerpt($issue->getBody(), 30); ?></small>
					</div>
				<?php endif; ?>

			</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="clear" style="height: 25px;"></div>
</div>