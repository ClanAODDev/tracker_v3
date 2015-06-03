<!-- If user is a dev or co/xo, they can view this issue-->
<?php if ($user->role > 2 || User::isDev()): ?>
<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Issue <?php echo $id ?></li>
	</ul>
	<div class='page-header'>
		<h2><strong>Issue Number <?php echo $id ?></strong></h2>
	</div>
		<h3><?php echo ucwords($selectIssue->getTitle()); ?></h3>
		<h4><small><?php echo "This issue was opened on " . substr($selectIssue->getCreatedAt(), 0, 10); ?></small></h4>
		<h4><small><?php echo "This issue was last edited on " . substr($selectIssue->getUpdatedAt(), 0, 10);?></small></h4>
		<h3>Labels</h3>
		<?php foreach($labels as $label): ?>
			<h4 text-color="<?php $label->getColor() ?>"><?php echo $label->getName()?>
		<?php endforeach; ?>
		<h3>Comments</h3>
		<?php foreach($comments as $comment): ?>
			<h4><?php echo $comment->getBody(); ?></h4>
			<br>		
		<?php endforeach; ?>
	<div class="clear" style="height: 25px;"></div>
</div>

<!-- If user is not a dev or co/xo, they cannot view this issue-->
<?php else: ?>
<div class='container'>
	<ul class='breadcrumb'>
		<li><a href='./'>Home</a></li>
		<li><a href='./issues'>Issue Tracker</a></li>
		<li class='active'>Issue <?php echo $id ?></li>
	</ul>
	<div class='page-header'>
		<h2><strong>Issue Number <?php echo $id ?> Is Not Available</strong></h2>
	</div>
	<div class="clear" style="height: 25px;"></div>
</div>
<?php endif ?>