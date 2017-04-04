<div class="panel panel-filled panel-c-danger collapsed">
    <div class="panel-heading panel-toggle">
        <div class="panel-tools">
            <i class="fa fa-chevron-up toggle-icon"></i>
        </div>
        <i class="fa fa-trash text-danger"></i> Delete {{ $division->locality('platoon') }}
    </div>
    <div class="panel-body">
        <p><span class="text-warning">WARNING:</span> Deleting this {{ $division->locality('platoon') }} will permanently remove it from your division. Any assigned members will be automatically unassigned, and will need to be reassigned to a new platoon.</p>

        <p>Deleting this {{ $division->locality('platoon') }} will:</p>

        <ul>
            <li>Unassign the current leader</li>
            <li>Delete any squads</li>
        </ul>
    </div>
    <div class="panel-footer">
        <button type="submit" class="btn btn-danger">Delete {{ $division->locality('platoon') }}</button>
    </div>
</div>

{{ csrf_field() }}
