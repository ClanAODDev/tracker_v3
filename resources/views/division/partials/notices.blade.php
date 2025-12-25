@if ($division->isShutdown())
    <x-notice type="danger">
        @if ($division->shutdown_at > now())
            This division will be shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should begin looking for new divisions.
        @else
            This division was shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should find new divisions or be removed immediately.
        @endif
    </x-notice>
@endif

@can('manage', \App\Models\MemberRequest::class)
    @php
        $pendingCount = $division->memberRequests()->pending()->count();
    @endphp
    @if ($pendingCount)
        <x-notice
            type="warning"
            icon="fa-user-plus"
            :cta="route('filament.mod.resources.member-requests.index') . '?tableFilters[status][value]=pending'"
            ctaLabel="Manage Requests"
        >
            There {{ $pendingCount === 1 ? 'is' : 'are' }} <code>{{ $pendingCount }}</code> pending {{ Str::plural('member request', $pendingCount) }}.
        </x-notice>
    @endif
@endcan

@if (($outstandingInactives ?? 0) && auth()->user()->isRole('sr_ldr'))
    <x-notice
        type="info"
        icon="fa-user-clock"
        :cta="route('division.inactive-members', $division)"
        ctaLabel="Process Inactives"
    >
        There {{ $outstandingInactives === 1 ? 'is' : 'are' }} <code>{{ $outstandingInactives }}</code> inactive {{ Str::plural('member', $outstandingInactives) }} exceeding <code>{{ config('aod.maximum_days_inactive') }}</code> days.
    </x-notice>
@endif

@if (($outstandingAwardRequests ?? 0) && auth()->user()->isDivisionLeader())
    <x-notice
        type="info"
        icon="fa-trophy"
        :cta="route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id)"
        ctaLabel="Manage Awards"
    >
        There {{ $outstandingAwardRequests === 1 ? 'is' : 'are' }} <code>{{ $outstandingAwardRequests }}</code> pending award {{ Str::plural('request', $outstandingAwardRequests) }} for approval.
    </x-notice>
@endif

@if (auth()->user()->isRole(['sr_ldr', 'jr_ldr']))
    @php
        $misconfiguredCount = $division->members()->misconfiguredDiscord()->count();
    @endphp
    @if ($misconfiguredCount)
        <x-notice
            type="info"
            icon="fa-headset"
            :cta="route('division.voice-report', $division->slug)"
            ctaLabel="View Report"
        >
            There {{ $misconfiguredCount === 1 ? 'is' : 'are' }} <code>{{ $misconfiguredCount }}</code> {{ Str::plural('member', $misconfiguredCount) }} with voice-comms issues.
        </x-notice>
    @endif
@endif

@if (count($division->unassigned) > 0)
    @can('create', [App\Models\Platoon::class, $division])
        <x-notice
            type="info"
            icon="fa-users"
            :cta="route('division', $division->slug) . '#platoons'"
            ctaLabel="Assign Members"
            class="scroll-to-organize"
        >
            There {{ count($division->unassigned) === 1 ? 'is' : 'are' }} <code>{{ count($division->unassigned) }}</code> unassigned {{ Str::plural('member', count($division->unassigned)) }} in <strong>{{ $division->name }}</strong>.
        </x-notice>
    @endcan
@endif

@if (count($pendingTransfers = $division->transfers()->pending()->get()) > 0 && auth()->user()->isDivisionLeader())
    <x-notice
        type="info"
        icon="fa-exchange-alt"
        :cta="route('filament.mod.resources.transfers.index') . '?tableFilters[incomplete][isActive]=true&tableFilters[transferring_to][value]=' . $division->id"
        ctaLabel="Manage Transfers"
    >
        There {{ count($pendingTransfers) === 1 ? 'is' : 'are' }} <code>{{ count($pendingTransfers) }}</code> pending incoming {{ Str::plural('transfer', count($pendingTransfers)) }}.
    </x-notice>
@endif