<?php if (empty($results)) : ?>
	<li class='text-muted list-group-item'>No results found.</li>
<?php else : ?>
	<?php $results = arrayToObject($results); ?>
	<?php foreach($results as $member) : ?>
		<?php $battlelog_name = ($member->battlelog_name != "0") ? $member->battlelog_name : "No BL name set"; ?>
		<a href='member/<?php echo $member->member_id ?>' class='list-group-item'><strong><?php echo ucwords($member->abbr) ?> <?php echo ucwords($member->forum_name) ?></strong><br /><span class="text-muted"><?php echo ucwords($battlelog_name) ?></span></a>	
	<?php endforeach; ?>
<?php endif; ?>