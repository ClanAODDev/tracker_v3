<div class="panel panel-filled panel-c-danger collapsed">
    <div class="panel-heading panel-toggle">
        <div class="panel-tools">
            <i class="fa fa-chevron-up toggle-icon"></i>
        </div>
        <i class="fa fa-trash text-danger"></i> Delete Division
    </div>
    <div class="panel-body">
        <p>
            <span class="text-warning">WARNING:</span> Deleting a division should only be done as a last resort. If you wish simply to deactivate the division (for instance, if the division is shut down), you should deselect the "active" option instead of deleting it.
        </p>
        <p>Only continue with the deletion of the division if you are certain this division will never be reactivated.</p>
    </div>
    <div class="panel-footer">
        <button type="submit" class="btn btn-danger">Delete Division</button>
    </div>
</div>

{{ csrf_field() }}
