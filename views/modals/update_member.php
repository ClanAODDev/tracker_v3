
<div class='modal-header'>
	<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'><i class='fa fa-times-circle'></i></span></button>
	<h4 class='modal-title'>Editing {$rank} {$forum_name}</h4>
</div>
<form id='edit-form'>
	<div class='modal-body' style='overflow-y: scroll; max-height: 400px;'>
		<div class='message alert' style='display: none;'></div>

		<input type='hidden' id='uid' name='uid' value='{$id}' />
		<input type='hidden' id='cur_plt' name='cur_plt' value='{$platoon_id}' />
		<input type='hidden' id='cur_sqd' name='cur_sqd' value='{$squadldr}' />
		<input type='hidden' id='cur_pos' name='cur_pos' value='{$cur_position}' />

		<div class='form-group'>
			<label for='forum_name' class='control-label'>Forum Name</label>
			<input type='text' class='form-control' id='forum_name' value='{$forum_name}' disabled>
		</div>

		<div class='form-group'>
			<label for='member_id' class='control-label'>Forum ID</label>
			<input type='number' class='form-control' id='member_id' value='{$member_id}'>
		</div>


		<div class='form-group battlelog-group'>
			<label for='battlelog' class='control-label'>Battlelog Name</label>
			<input type='text' class='form-control' id='battlelog' value='{$battlelog_name}'>
		</div>

		<div class='form-group'>
			<label for='recruiter' class='control-label'>Recruiter ID</label>
			<input type='number' class='form-control' id='recruiter' value='{$recruiter}'>
		</div>

		<div class='form-group platoon-group' style='display: {$assignmentPltFieldDisplay}'>
			<label for='platoon' class='control-label'>Platoon</label>
			<select name='platoon' id='platoon' class='form-control'>{$platoons}</select>
		</div>

		<div class='form-group sqdldr-group' style='display: {$assignmentSqdFieldDisplay}'>
			<label for='sqdldr' class='control-label'>Squad Leader</label>
			<select name='sqdldr' id='sqdldr' class='form-control'>{$squadLeaders}</select>
		</div>

		<div class='form-group position-group' style='display: {$assignmentPosFieldDisplay}'>
			<label for='position' class='control-label'>Position</label>
			<select name='position' id='position' class='form-control'>{$positions}</select>
		</div>


	</div>
	<div class='modal-footer'>
		<button type='submit' class='btn btn-block btn-success'>Save Info</button> 
	</form>
</div>

<script src='assets/js/view.js'></script>