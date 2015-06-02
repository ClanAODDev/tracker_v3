<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Issue <?php echo $id ?></li>
	</ul>

	<div class='page-header'>
		<h2>
			<strong>Issue Tracker</strong><br><small>Issue Number <?php echo $id ?></small>
		</h2>
	</div>

	<?php if (count($selectIssue)): ?>
		<?php foreach($selectIssue as $issue): ?>
				<a class="list-group-item" href="./issues/<?php echo $issue->getNumber(); ?>">

				<span class="text-muted">#<?php echo ucwords($issue->getNumber()); ?> </span>

				<strong><?php echo ucwords($issue->getTitle()); ?></strong>

				<?php if ($issue->getComments()): ?>
					<span class="badge"><i class="fa fa-comment"></i> <?php echo $issue->getComments() ?></span>
				<?php endif; ?>

			</a>
		<?php endforeach; ?>
	<?php endif; ?>
	<div class="clear" style="height: 25px;"></div>
</div>