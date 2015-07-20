<div class='panel panel-primary'>
	<div class='panel-heading'><strong><?php echo getUserRoleName($user->role); ?> Quick Tools</strong></div>
	<div class='list-group'>
		<?php if (count($tools)) : ?>
			<?php foreach($tools as $tool) : ?>
				<?php $disabled = ($tool->disabled) ? "disabled" : null; ?>
				<a href='<?php echo $tool->tool_path ?>' class='list-group-item <?php echo $tool->class . " " . $disabled ?>'>
					<h4 class='list-group-item-heading'><strong><?php echo $tool->tool_name ?></strong><i class='pull-right fa fa-<?php echo $tool->icon ?> fa-lg text-muted'></i></h4>
					<p class='list-group-item-text text-muted hidden-xs hidden-sm'><?php echo $tool->tool_descr ?></p>
				</a>			
			<?php endforeach; ?>				
		<?php else : ?>
			<li class='list-group-item'>No tools currently available to you</li>
		<?php endif; ?>
	</div>
</div>