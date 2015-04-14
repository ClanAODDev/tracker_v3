<ul class="list-group thread-list text-left">


	<?php foreach ($gameThreads as $gameThread) : ?>

		<?php $status = DivisionThread::checkForPost($player, $gameThread->thread_url); ?>
		<li class="list-group-item thread"><?php echo $gameThread->thread_title ?> <i class='fa fa-copy copy-button-rct text-primary' title='Copy link to clipboard' href='#' data-clipboard-text='<?php echo $gameThread->thread_url ?>'></i><?php echo ($status) ? "<span class=\"badge alert-success\"><i class=\"fa fa-check fa-lg\"></i></span>" : "<span class=\"badge alert-danger\" title=\"User has not completed this step\"><i class=\"fa fa-times fa-lg\"></i></span>"; ?>
		</li>

	<?php endforeach; ?>

</ul>

<div class='text-left'>
	<span class="reload text-muted" style="cursor: pointer;">Refresh Thread Check <i class="fa fa-refresh"></i></span>
	<span class='thread-status pull-right text-danger'></span>
</div>


<script src="assets/js/check_threads.js"></script>