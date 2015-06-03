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
		<h3><?php echo ucwords($selectIssue->getTitle()); ?></h3>
		<h4><small><?php echo "This issue was opened on " . substr($selectIssue->getCreatedAt(), 0, 10); ?></small></h4>
		<h4><small><?php echo "This issue was last edited on " . substr($selectIssue->getUpdatedAt(), 0, 10);?></small></h4>
		<?php var_dump($comments);?>
	<div class="clear" style="height: 25px;"></div>
</div>