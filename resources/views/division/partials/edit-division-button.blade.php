@can('update', $division)
    <a href="{{ route('editDivision', $division->abbreviation) }}"
       title="Edit {{ $division->name }}"
       class="btn btn-default"><i class="fa fa-wrench"></i></a>
@endcan