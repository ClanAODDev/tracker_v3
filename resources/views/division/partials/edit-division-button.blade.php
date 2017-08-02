@can('update', $division)
    <a href="{{ route('editDivision', $division->abbreviation) }}"
       title="Edit {{ $division->name }}" class="btn btn-default pull-right">
        <i class="fa fa-wrench text-accent"></i> Manage Division
    </a>
@endcan