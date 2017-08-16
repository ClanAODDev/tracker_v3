<div class="pull-right">
    @can('update', $platoon)
        <a href="{{ route('platoon.manage-squads', [$division->abbreviation, $platoon]) }}" title="Manage squads"
           class="btn btn-default "><i class="fa fa-users text-accent"></i>
            Manage Members
        </a>

        <a href="{{ route('editPlatoon', [$division->abbreviation, $platoon]) }}"
           title="Edit {{ $platoon->name }}" class="btn btn-default">
            <i class="fa fa-wrench text-accent"></i> Manage {{ $division->locality('platoon') }}
        </a>
    @endcan

    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
        <i class="fa fa-bullhorn text-accent"></i> Mass PM
    </button>
</div>


