<div class="page-404">	
	<div class="error-code">404</div>
	<div class="error-text">
		<span class="oops">Aww, crap</span><br>
		<span class="hr"></span>
		<br>
		<?php if (!isset($_SESSION['userid'])) : ?>
			<p>Your session has expired. Please log back in.</p>
			<p class="margin-top-50"><a href="./" class="btn btn-2x btn-info"><i class="fa fa-user"></i> Log In</a></p>
		<?php else : ?>
			<p>That page doesn't exist... or you don't have access to it.</p>
			<p class="margin-top-50"><a href="./" class="btn btn-2x btn-info"><i class="fa fa-home"></i> Take Me Home</a></p>
		<?php endif; ?>
	</div> <!-- / .error-text -->
	<div class="margin-top-50"></div>
</div>



