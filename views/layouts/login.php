<div class="container login-form fade-in" style="z-index: 5;">
	<div class="panel panel-primary" id="login-panel">
		<div class="panel-heading text-left"><small><strong>AOD</strong> Member Login<i class="fa fa-user pull-right fa-2x"></i></small></div>
		<div class="panel-body">
			<form class="form" role="form" action="/application/ajax/login.php" method="post" id="login">

				<div class="form-group">
					<label class="sr-only" for="user">Username</label>
					<input type="text" class="form-control" id="user" name="user" placeholder="Username" required />
				</div>

				<div class="form-group">
					<label class="sr-only" for="password">Password</label>
					<input type="password" class="form-control" id="password" name="password" placeholder="Password" required />
				</div>
				<button type="submit" class="btn btn-primary login-btn">Log in </button>
			</form>
			
		</div>
		<div class="panel-footer text-muted"><small>Don"t have an account? <a href="./register/">Create one</a>.</small></div>
	</div>
	<div class="msg"></div>
	<div class="status-text"></div>
</div>