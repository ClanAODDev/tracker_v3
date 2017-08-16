<div class="pull-right">
    @can('update', $platoon)
        <a href="{{ route('platoon.manage-squads', [$division->abbreviation, $platoon]) }}" title="Manage squads"
           class="btn btn-default "><i class="fa fa-users text-accent"></i>
            <span class="hidden-xs hidden-sm">Manage Members</span>
        </a>

        <a href="{{ route('editPlatoon', [$division->abbreviation, $platoon]) }}"
           title="Edit {{ $platoon->name }}" class="btn btn-default">
            <i class="fa fa-wrench text-accent"></i>
            <span class="hidden-xs hidden-sm">Manage {{ $division->locality('platoon') }}</span>
        </a>
    @endcan

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
        <i class="fa fa-bullhorn text-accent"></i> <span class="hidden-xs hidden-sm">Mass PM</span>
    </button>
</div>


