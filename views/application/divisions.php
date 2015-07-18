<div class='panel panel-default'>
	<div class='panel-heading'><i class='fa fa-gamepad fa-lg pull-right text-muted'></i> <strong>Gaming Divisions</strong></div>
	<div class='list-group'>
		<?php foreach ($divisions as $division) : ?>
			<a href='divisions/<?php echo $division->short_name ?>' class='list-group-item' style='padding-bottom: 18px;'>
				<span class='pull-left' style='margin-right: 20px; vertical-align: middle;'><img src='assets/images/game_icons/48x48/<?php echo $division->short_name ?>.png' /></span>
				<h4 class='list-group-item-heading'><strong><?php echo $division->full_name ?></strong></h4>
				<p class='list-group-item-text text-muted'><?php echo $division->short_descr ?></p>
				<h5 class="pull-right text-muted big-num-main"><span class="count-animated"><?php echo Division::totalCount($division->id)->count; ?></span></h5>
			</a>
		<?php endforeach; ?>
	</div>
</div>