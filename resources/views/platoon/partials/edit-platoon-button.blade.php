@can('update', $platoon)
    <a href="{{ route('editPlatoon', [$division->abbreviation, $platoon]) }}"
       title="Edit {{ $platoon->name }}"
       class="btn btn-default"><i class="fa fa-wrench"></i></a>
@endcan