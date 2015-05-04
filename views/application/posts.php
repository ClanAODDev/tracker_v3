<?php if (count($posts)) : ?>
	<?php foreach($posts as $post) : ?>
		<?php $member_id = Member::findById($post->member_id)->member_id; ?>
		<?php $member = Member::profileData($member_id); ?>
		<div class='panel panel-default'>
			<div class='panel-heading'><?php echo Member::avatar($member->member_id) . " " .  $post->title; ?></div>
			<div class='panel-body'><?php echo $post->content ?></div>
			<div class='panel-footer text-muted text-right'>
				<small>Posted <?php echo date("Y-m-d", strtotime($post->date)); ?> by <a href='member/<?php echo $member->member_id; ?>'><?php echo Member::findForumName($member->member_id) ?></a></small>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>