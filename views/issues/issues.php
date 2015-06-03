<div class="panel panel-default">
	<div class="panel-heading"><strong><?php echo ucwords($filter); ?> Issues</strong></div>

	<?php if (count($issues)): ?>
		<div class="list-group">
		<?php foreach($issues as $issue): ?>
				<a class="list-group-item" href="./issues/<?php echo $issue->getNumber(); ?>">
					<span class="text-muted">#<?php echo ucwords($issue->getNumber()); ?> </span>
					
					<strong><?php echo ucwords($issue->getTitle()); ?></strong>
					<?php if ($issue->getComments()): ?>
						<span class="badge"><i class="fa fa-comment"></i> <?php echo $issue->getComments() ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>