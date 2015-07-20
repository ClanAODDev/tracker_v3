<?php $division = Division::findById($_POST['division_id']); ?>
<?php $platoon = Platoon::findById($_POST['platoon_id']); ?>
<?php $leaders = Platoon::SquadLeaders($_POST['division_id'], $_POST['platoon_id']); ?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title"><strong>Create</strong> New Squad</h4>
</div>

<form id="create_squad">

	<div class="modal-body">

		<p>Select a squad leader to assign to your new squad. Or select none to create a squad without a leader, to be assigned later. If the player you want is not listed, ensure that they are set as a squad leader through their tracker profile, that they are assigned to the correct platoon, and that they aren't already a leader of an existing squad.</p>

		<p>Modifying: <strong><?php echo $platoon->name ?></strong> of the <strong><?php echo $division->full_name ?></strong> division</p>

		<input type='hidden' name='platoon_id' value='<?php echo $_POST['platoon_id'] ?>'></input>
		<input type='hidden' name='division_id' value='<?php echo $_POST['division_id'] ?>'></input>


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
		<button type="button" class="btn btn-success" id="create_squad_btn">Create Squad</button>
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>

</form>