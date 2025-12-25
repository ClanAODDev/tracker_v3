@if ($member->memberRequest && $member->memberRequest->approved_at === null)
    <x-notice type="warning" icon="fa-clock">
        This member's status request is currently pending. Some details, like Discord VoIP activity, may not be available until the request is approved.
    </x-notice>
@endif

@can ('update', $member->clan_id)
    @if (! $member->handles->contains($division->handle) && $division->handle)
        <x-notice
            type="warning"
            icon="fa-gamepad"
            :cta="route('editMember', $member->clan_id) . '#handles'"
            ctaLabel="Add Handle"
        >
            The <strong>{{ $division->name }}</strong> division requires a <code>{{ $division->handle->label }}</code> handle, but {{ $member->name }} does not have one.
        </x-notice>
    @endif
@endcan

@if ($member->flagged_for_inactivity)
    <x-notice
        type="warning"
        icon="fa-flag"
        :cta="route('member.unflag-inactive', $member->clan_id) . '#flagged'"
        ctaLabel="Remove Flag"
    >
        Member is flagged for removal due to inactivity.
    </x-notice>
@endif

@if ($member->leave)
    @if ($member->leave->approver)
        <x-notice
            type="warning"
            icon="fa-calendar-alt"
            :cta="route('filament.mod.resources.leaves.edit', $member->leave->id)"
            ctaLabel="View Details"
        >
            Member has a leave of absence for <strong>{{ $member->leave->reason }}</strong> until <strong>{{ $member->leave->end_date->format('Y-m-d') }}</strong>.
        </x-notice>
    @else
        <x-notice
            type="warning"
            icon="fa-calendar-alt"
            :cta="route('filament.mod.resources.leaves.edit', $member->leave->id)"
            ctaLabel="View Details"
        >
            Member has a leave of absence request that has not yet been approved.
        </x-notice>
    @endif
@endif

