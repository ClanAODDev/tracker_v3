@can('update', $member)
    <a href="{{ route('editMember', $member->clan_id) }}"
       title="Edit {{ $member->name }}"
       class="btn btn-default pull-right"><i class="fa fa-wrench text-accent"></i> Edit
    </a>
@endcan