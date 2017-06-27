@can ('update', $member->clan_id)
    @if (! $member->handles->contains($division->handle) && $division->handle)
        <div class="alert alert-warning">
            The {{ $division->name }} division requires a(n)
            <code>{{ $division->handle->label }}</code> handle, but {{ $member->name }} does not have one. You should
            <a href="{{ route('editMember', $member->clan_id) }}#handles" class="alert-link">add it now</a>.
        </div>
    @endif
@endcan

@if ($member->has('leaveOfAbsence'))
    @if ($member->leaveOfAbsence->approver)
        <div class="alert alert-warning">
            Member has a leave of absence in place. <a class="alert-link"
                                                       href="{{ route('member.edit-leave', [$member->clan_id, $member->leaveOfAbsence->id]) }}">View Details</a>
        </div>
    @else
        <div class="alert alert-warning">
            Member has a leave of absence request that has not yet been approved.
            <a class="alert-link"
               href="{{ route('member.edit-leave', [$member->clan_id, $member->leaveOfAbsence->id]) }}">View Details</a>
        </div>
    @endif
@endif

