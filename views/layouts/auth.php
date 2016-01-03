<div class="container auth-form">
	<div class='page-header'>
		<h2><strong>Authenticate <small>Your Account</small></strong></h2>
	</div>
	<p>Periodically you may receive alerts from this application based on your account level. For this reason, you are required to register with a valid email. This application also relies on email for password reset. To authenticate your account, please enter the email you used to register with.</p>
	<div class="row">
		<div class="col-md-12">
			<div class="alert" style="display:none;"></div>
			<form class="form" role="form" action="do/validate-email" method="get" id="verify">
			<input type="hidden" value="<?php echo (isset($_GET['id'])) ? $_GET['id'] : NULL; ?>" name="validation" />
				<div class="panel panel-primary">
					<div class="panel-heading">Verify Email</div>
					<div class="panel-body">
						<div class="input-group">
							<label class="sr-only" for="email">Email</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="Email" required />
							<span class="input-group-btn">
								<button class="btn btn-primary submit-btn" type="submit">Submit</button>
								<button class="btn btn-default resend-btn">Resend Verification</button>
							</span>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
