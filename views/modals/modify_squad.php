<?php $leaders = Platoon::SquadLeaders($_POST['division_id'], $_POST['platoon_id']); ?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title"><strong>Modify</strong> Squad</h4>
</div>

<form id="modify_squad">

	<div class="modal-body">

		<p>Select a squad leader to assign to this squad. Or select none to create a squad without a leader, to be assigned later. If the player you want is not listed, ensure that they are set as a squad leader through their tracker profile, that they are assigned to the correct platoon, and that they aren't already a leader of an existing squad.</p>

		<input type='hidden' name='squad_id' value='<?php echo $_POST['squad_id'] ?>'></input>

		<div class="form-group">
			<select name="leader_id" class="form-control">

				<?php if (count((array) $leaders)): ?>
					<?php foreach($leaders as $leader): ?>
						<option value="<?php echo $leader->id ?>"><?php echo Rank::convert($leader->rank_id)->abbr . " " . ucwords($leader->forum_name); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
				<option value="0">None</option>

			</select>
		</div>

	</div>

	<div class="modal-footer">	
		<button type="button" class="btn btn-success" id="modify_squad_btn">Modify Squad</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>

</form>