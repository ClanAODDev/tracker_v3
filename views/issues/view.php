<div class='container'>

	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Issue #<?php echo $issue->getNumber() ?></li>
	</ul>

	<div class='page-header'>
		<h2><strong><?php echo $issue->getTitle(); ?></strong> <small>#<?php echo $issue->getNumber(); ?></small></h2><small class="text-muted">Opened <?php echo date("F jS, Y, g:i a", strtotime($issue->getCreatedAt())); ?></small>
	</div>



	<h3><p>About The Issue</p></h3>
	<div class='GitHubIssues'><p><?php echo $issue->getBody() ?: "No description exists for this issue"; ?></p></div>

	<?php if ($issue->getAssignee()): ?>
		<h3>Assigned Developer</h3>
		<div class='GitHubIssues'><p>
			<?php $url = $issue->getAssignee()->getAvatarUrl(); ?>
			<?php echo "<img src='$url' width=30, height=30 />";?>
			<?php echo $issue->getAssignee()->getLogin(); ?>
		</p></div>
	<?php endif; ?>

	<h3>Comments</h3><hr/>
	<?php if (count($comments)): ?>

		<?php foreach($comments as $comment): ?>
			<div class='GitHubIssues'><p>
				<?php $url = $comment->getUser()->getAvatarUrl(); ?>
				<?php echo "<img src='$url' width=35, height=35/>";?>
				<?php echo $comment->getBody(); ?>
			</p></div>	
		<?php endforeach; ?>

	<?php else: ?>
		<div class='GitHubIssues'><p>There are no comments for this issue.</p></div>
	<?php endif; ?>

	<hr />
	<small>Last updated on <?php echo date("m D Y, H:i:s", strtotime($issue->getUpdatedAt()));?></small>

</div>