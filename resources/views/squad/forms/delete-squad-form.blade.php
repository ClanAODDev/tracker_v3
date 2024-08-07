<form action="{{ route('deleteSquad', [$division->slug, $platoon, $squad]) }}" method="post">
    @csrf
    @method('delete')
    <div class="panel panel-filled panel-c-danger collapsed">
        <div class="panel-heading panel-toggle">
            <div class="panel-tools">
                <i class="fa fa-chevron-up toggle-icon"></i>
            </div>
            <i class="fa fa-trash text-danger"></i> Delete {{ $division->locality('squad') }}
        </div>
        <div class="panel-body">
            <span class="text-warning">WARNING:</span> Deleting this {{ $division->locality('squad') }} will permanently
            remove it from your division. Any assigned members will be automatically unassigned, and will need to be
            reassigned to a new {{ $division->locality('squad') }}.
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-danger">Delete {{ $division->locality('squad') }}</button>
        </div>
    </div>
</form>