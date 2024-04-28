@can('update', $division)
    <a href="{{ route('.*', $division->slug) }}"
       title="Edit {{ $division->name }}" class="btn btn-default pull-right">
        <i class="fa fa-wrench text-accent"></i> <span class="hidden-xs">Manage Division</span>
    </a>
@endcan