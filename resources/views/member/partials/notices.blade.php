@if ($member->isPending)
    <div class="alert alert-warning">
       This member's status request is currently pending. Some details, like teamspeak activity, may not be available until the request is approved.
    </div>
@endif
@can ('update', $member->clan_id)
    @if (! $member->handles->contains($division->handle) && $division->handle)
        <div class="alert alert-warning">
            The {{ $division->name }} division requires a(n)
            <code>{{ $division->handle->label }}</code> handle, but {{ $member->name }} does not have one. You should
            <a href="{{ route('editMember', $member->clan_id) }}#handles" class="alert-link">add it now</a>.
        </div>
    @endif
@endcan


@if ($member->leave)
    @if ($member->leave->approver)
        <div class="alert alert-warning">
            Member has a leave of absence in place.
            <a class="alert-link"
               href="{{ route('leave.edit', [$member->clan_id, $member->leave->id]) }}">View Details</a>
        </div>
    @else
        <div class="alert alert-warning">
            Member has a leave of absence request that has not yet been approved.
            <a class="alert-link"
               href="{{ route('leave.edit', [$member->clan_id, $member->leave->id]) }}">View Details</a>
        </div>
    @endif
@endif

