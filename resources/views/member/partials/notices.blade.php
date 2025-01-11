@if ($member->memberRequest()->exists() && $member->memberRequest->approved_at == null)
    <div class="alert alert-warning">
        This member's status request is currently pending. Some details, like Discord VoIP activity, may not be
        available until the request is approved.
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

@if ($member->flagged_for_inactivity)
    <div class="alert alert-warning">
        <i class="fa fa-flag"></i> Member is flagged for removal. <a
                href="{{ route('member.unflag-inactive', $member->clan_id) . "#flagged" }}"
                class="alert-link pull-right">
            Remove flag
        </a>
    </div>
@endif

@if ($member->leave()->exists())
    @if ($member->leave->approver)
        <div class="alert alert-warning">
            Member has a leave of absence in place for [<strong>{{ $member->leave->reason }}</strong>] until
            [<strong>{{ $member->leave->end_date->format('Y-m-d') }}</strong>].
            <a class="alert-link"
               href="{{ route('filament.mod.resources.leaves.edit', $member->leave->id) }}">View Details</a>
        </div>
    @else
        <div class="alert alert-warning">
            Member has a leave of absence request that has not yet been approved.
            <a class="alert-link"
               href="{{ route('filament.mod.resources.leaves.edit', $member->leave->id) }}">View Details</a>
        </div>
    @endif
@endif

