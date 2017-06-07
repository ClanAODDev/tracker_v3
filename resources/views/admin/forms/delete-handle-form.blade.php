<div class="panel panel-filled panel-c-danger collapsed">
    <div class="panel-heading panel-toggle">
        <div class="panel-tools">
            <i class="fa fa-chevron-up toggle-icon"></i>
        </div>
        <i class="fa fa-trash text-danger"></i> Delete Handle
    </div>
    <div class="panel-body">
        <p>
            <span class="text-warning">WARNING:</span> Only delete a handle once you have ensured no divisions are currently using it as a primary ingame handle. Additionally, any ingame handle data stored for members will be destroyed.
        </p>
        <p>Only continue with the deletion of the handle if you are certain this data is no longer needed.</p>
    </div>
    <div class="panel-footer">
        <button type="submit" class="btn btn-danger">Delete Handle</button>
    </div>
</div>

{{ csrf_field() }}
