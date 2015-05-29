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

	<div class="list-group">

		<?php if (count($issues)): ?>

			<?php foreach($issues as $issue): ?>

				<div class="list-group-item"><strong><?php echo ucwords($issue->getTitle()); ?></strong>
					<div class"list-group-item-text text-muted"><?php echo ucwords($issue->getBody()); ?> </div>
				</div>
								
			<?php endforeach; ?>

		<?php endif; ?>

	</div>
</div>