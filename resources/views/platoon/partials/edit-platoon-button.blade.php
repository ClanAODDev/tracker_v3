@can('update', $platoon)
    <div class="pull-right">
        <a href="{{ route('platoon.manage-squads', [$division->abbreviation, $platoon]) }}" title="Manage squads"
           class="btn btn-default "><i class="fa fa-cogs text-accent"></i> Manage Squads
        </a>

        <a href="{{ route('editPlatoon', [$division->abbreviation, $platoon]) }}"
           title="Edit {{ $platoon->name }}"
           class="btn btn-default"><i class="fa fa-wrench text-accent"></i> Edit
        </a>
    </div>
@endcan