<?php if (count($notifications)) : ?>
	<?php foreach($notifications as $notification) { echo $notification; } ?>
<?php endif; ?>
<?php if (count($alerts)) : ?>
	<?php foreach($alerts as $alert) : ?>
		<div data-id="<?php echo $alert->id; ?>" data-user="<?php echo $user->id; ?>" class="alert-dismissable alert alert-<?php echo $alert->type; ?> fade in" role="alert">
			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
			</button>
			<?php echo $alert->content; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>