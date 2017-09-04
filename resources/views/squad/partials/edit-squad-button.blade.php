<div class="pull-right">
    @can('update', $squad)
        <a href="{{ route('editSquad', [$division->abbreviation, $platoon, $squad]) }}"
           title="Edit {{ $platoon->name }}" class="btn btn-default">
            <i class="fa fa-wrench text-accent"></i>
            <span class="hidden-xs hidden-sm">Manage {{ $division->locality('squad') }}</span>
        </a>
    @endcan
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mass-pm-modal">
        <i class="fa fa-bullhorn text-accent"></i> <span class="hidden-xs hidden-sm">Mass PM</span>
    </button>
</div>