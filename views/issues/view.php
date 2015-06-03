<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'><?php echo ucwords($selectIssue->getTitle()); ?></li>
	</ul>

	<div class='page-header'>
		<h2>
			<?php echo ucwords($selectIssue->getTitle()); ?> <small><span class="text-muted">#<?php echo $id ?></span></small></h2>
		</div>

		<h4><small><?php echo "This issue was opened on " . substr($selectIssue->getCreatedAt(), 0, 10); ?></small></h4>
		<h4><small><?php echo "This issue was last edited on " . substr($selectIssue->getUpdatedAt(), 0, 10);?></small></h4>
		<div class="clear" style="height: 25px;"></div>
	</div>