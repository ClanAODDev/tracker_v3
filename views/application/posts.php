<?php if (count($posts)) : ?>
	<?php foreach($posts as $post) : ?>
		<div class='panel panel-default'>
			<div class='panel-heading'><?php echo Member::avatar($post->forum_id) .  $post->title; ?></div>
			<div class='panel-body'><?php echo $post->content ?></div>
			<div class='panel-footer text-muted text-right'>
				<small>Posted <?php echo date("Y-m-d", strtotime($post->date)); ?> by <a href='/member/{$authorId}'><?php echo Member::findForumName($post->forum_id) ?></a></small>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>