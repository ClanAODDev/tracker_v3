<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title">Report Bug</h4>
</div>

<form id='submit-issue' action='#'>

	<div class="modal-body">
		<p>Include as much information as possible about the issue you are having. You will be able to follow up on your report after it is submitted.</p>
		
		<input type='hidden' name='user' value='<?php echo $user->id ?>'></input>
		<div class="form-group">
			<input type='text' class='form-control' name='title' placeholder='Title' required></input>
		</div>
		<div class="form-group">
			<input type='text' class='form-control' name='link' placeholder='Link where problem occurred' required></input>
		</div>
		<div class="form-group">
			<textarea rows="5" class='form-control' name='body' placeholder='Detailed explanation' required></textarea>
		</div>
		
	</div>

	<div class="modal-footer">	
		<button type="submit" class="btn btn-success">Submit Issue</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>

</form>