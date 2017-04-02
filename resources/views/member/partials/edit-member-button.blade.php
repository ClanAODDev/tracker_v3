@can('update', $member)
    <a href="{{ route('editMember', $member->clan_id) }}"
       title="Edit {{ $member->name }}"
       class="btn btn-default"><i class="fa fa-wrench"></i></a>
@endcan