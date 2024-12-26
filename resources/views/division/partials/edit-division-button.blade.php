@can('update', $division)
    <a href="{{ route('filament.mod.resources.divisions.edit', $division) }}"
       title="Edit {{ $division->name }}" class="btn btn-default pull-right">
        <i class="fa fa-wrench text-accent"></i> <span class="hidden-xs">Manage Division</span>
    </a>
@endcan