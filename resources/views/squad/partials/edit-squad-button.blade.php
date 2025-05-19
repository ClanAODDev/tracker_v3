<div class="pull-right">
    @can('update', $squad)
        <a href="{{ route('filament.mod.resources.squads.edit', [$squad]) }}"
           title="Edit {{ $platoon->name }}" class="btn btn-default">
            <i class="fa fa-wrench text-accent"></i>
            <span class="hidden-xs hidden-sm">Manage {{ $division->locality('squad') }}</span>
        </a>
    @endcan
</div>