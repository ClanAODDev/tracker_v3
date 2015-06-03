<div class="panel panel-default">
	<div class="panel-heading"><i class="fa fa-filter"></i> Filter by state</div>
	<div class="list-group">
		<a class="list-group-item <?php echo ($filter == "open") ? "active" : NULL; ?>" href="issues/open">Open Issues</a>
		<a class="list-group-item <?php echo ($filter == "closed") ? "active" : NULL; ?>" href="issues/closed">Closed Issues</a>
		<?php if ($user->role > 2 || User::isDev()): ?>
			<a class="list-group-item <?php echo ($filter == "dev") ? "active" : NULL; ?>" href="issues/dev">Development Issues</a>
		<?php endif; ?>
	</div>
</div>
