<?php if (empty($results)) : ?>
	<li class='text-muted list-group-item'>No results found.</li>
<?php else : ?>
	<?php $results = arrayToObject($results); ?>
	<?php foreach($results as $member) : ?>
		<a href='member/<?php echo $member->member_id ?>' class='list-group-item'><strong><?php echo ucwords($member->abbr) ?> <?php echo ucwords($member->forum_name) ?></strong></a>
	<?php endforeach; ?>
<?php endif; ?>
