<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li class='active'>Bug Issues and Reports</li>
	</ul>

	<div class='page-header'>
		<h2>
			<strong>Bug Issues and Reports</strong>
		</h2>
	</div>

	<?php if (count($issues)): ?>
		<?php foreach($issues as $issue): ?>
			<div class="panel panel-default">

				<div class="panel-heading">
					<a href="./issues/<?php echo $issue->getNumber(); ?>"><strong><?php echo ucwords($issue->getTitle()); ?></strong></a> &mdash;
					<small class="comment-count"><i class="fa fa-comment text-muted"></i> <?php echo $issue->getComments() ?></small>
					<span class="pull-right"> <?php echo Github::convertState($issue->getState()); ?></span>
				</div>

				<div class="panel-body">
					<span class="text-muted"><?php echo excerpt($issue->getBody(), 30); ?></span>
				</div>

				<div class="panel-footer">
					<span class="text-muted">Lat updated <?php echo formatTime(strtotime($issue->getUpdatedAt())); ?></span>
				</div>

			</div>
		<?php endforeach; ?>
	<?php endif; ?>

</div>