@can('update', $squad)
    <div class="pull-right">
        <a href="{{ route('editSquad', [$division->abbreviation, $platoon, $squad]) }}"
           title="Edit {{ $platoon->name }}" class="btn btn-default">
            <i class="fa fa-wrench text-accent"></i> Manage {{ $division->locality('squad') }}
        </a>
    </div>
@endcan