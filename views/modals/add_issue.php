<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title">Report Bug</h4>
</div>

<div class="modal-body">
	<p>Include as much information as possible about the issue you are having. Be sure to include a link to the page where the problem occurred.</p>
	<form id='submit-issue' action='#'>
		<input type='hidden' name='user' value='<?php echo $user->id ?>'></input>
		<div class="form-group">
			<input type='text' class='form-control' name='title' placeholder='Title' required></input>
		</div>
		<div class="form-group">
			<textarea class='form-control' name='body' placeholder='Detailed explanation' required></textarea>
		</div>
	</form>
</div>

<div class="modal-footer">	
	<button type="button" class="btn btn-success">Submit Issue</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>