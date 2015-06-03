<div class="panel panel-default">
	<div class="panel-heading"><strong><?php echo ucwords($filter); ?> Issues</strong></div>

	<?php if (count($issues)): ?>
		<div class="list-group">
			<?php foreach($issues as $issue): ?>
				<a class="list-group-item" href="./issues/view/<?php echo $issue->getNumber(); ?>">
					
					<h4 class="list-group-item-heading"><?php echo ucwords($issue->getTitle()); ?> <span class="text-muted">#<?php echo ucwords($issue->getNumber()); ?> </span></h4>

					<p class="list-group-item-text text-muted"><?php echo excerpt($issue->getBody(), 20); ?></p>

					<?php if ($issue->getComments()): ?>
						<span class="badge"><i class="fa fa-comment"></i> <?php echo $issue->getComments() ?></span>
					<?php endif; ?>

				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>