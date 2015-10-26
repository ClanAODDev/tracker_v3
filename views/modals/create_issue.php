<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title">Report New Issue</h4>
</div>

<form id="submit-issue">

	<div class="modal-body">
		<p>Include as much information as possible about the issue you are having.</p>

		<input type='hidden' name='user' value='<?php echo $_SESSION['memberid'] ?>'></input>
		<div class="form-group">
			<input type='text' class='form-control' name='title' id='title' placeholder='Issue title' required></input>
		</div>
		<div class="form-group">
			<input type='text' class='form-control' name='link' placeholder='Link where problem occurred' required></input>
		</div>
		<div class="form-group">
			<textarea rows="5" class='form-control' name='body' placeholder='Detailed explanation' required></textarea>
		</div>

	</div>

	<div class="modal-footer">
		<button type="button" class="btn btn-success" id="submit_btn">Submit Issue</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>

</form>
