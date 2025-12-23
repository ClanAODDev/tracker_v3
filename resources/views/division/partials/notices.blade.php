@if ($division->isShutdown())
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i>
        @if ($division->shutdown_at > now())
            This division will be shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should begin looking for new divisions.
        @else
            This division was shut down on <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should find new divisions or be removed immediately.
        @endif
    </div>
@endif

{{-- pending member requests --}}
@can('manage', \App\Models\MemberRequest::class)
    @php
        $pendingCount   = $division->memberRequests()->pending()->count();
    @endphp

    @if ($pendingCount)
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-circle"></i>
            There {{ $pendingCount === 1 ? 'is' : 'are' }} <code>{{ $pendingCount }}</code> pending {{ Str::plural('member request', $pendingCount) }}.
            <a href="{{ route('filament.mod.resources.member-requests.index')  . '?tableFilters[status][value]=pending'}}" class="alert-link
            pull-right">Manage Member
                Requests</a>
        </div>
    @endif
@endcan

{{-- pending award requests --}}
@if ($division->outstandingInactives && auth()->user()->isRole('sr_ldr'))
    <div class="alert alert-default">
        <i class="fa fa-user-clock"></i>
        There {{ $division->outstandingInactives === 1 ? 'is' : 'are' }} <code>{{ $division->outstandingInactives }}</code> inactive {{ Str::plural('member', $division->outstandingInactives) }} whose last activity exceeds <code>{{ config('aod.maximum_days_inactive') }}</code> days. Please <a href="{{ route('division.inactive-members', $division) }}" class="alert-link">process these members</a> out of AOD.
    </div>
@endif

{{-- pending transfers --}}
@if ($division->outstandingAwardRequests && auth()->user()->isDivisionLeader())
    <div class="alert alert-default">
        <i class="fa fa-trophy"></i>
        There {{ $division->outstandingAwardRequests === 1 ? 'is' : 'are' }} <code>{{ $division->outstandingAwardRequests }}</code> pending award {{ Str::plural('request', $division->outstandingAwardRequests) }} for approval.
        <a href="{{ route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id) }}" class="alert-link">Manage Requests</a>
    </div>
@endif

{{-- misconfigured comms --}}
@if ($division->members()->misconfiguredDiscord()->count() && auth()->user()->isRole(['sr_ldr', 'jr_ldr']))
    <div class="alert alert-default">
        <i class="fa fa-comments"></i>
        There {{ $division->members()->misconfiguredDiscord()->count() === 1 ? 'is' : 'are' }} member{{ $division->members()->misconfiguredDiscord()->count() === 1 ? '' : 's' }} with voice‐comms issues. Please review the <a href="{{ route('division.voice-report', $division->slug) }}" class="alert-link">Voice Comms Report</a>.
    </div>
@endif

{{-- unassigned members --}}
@if (count($division->unassigned) > 0)
    @can('create', [App\Models\Platoon::class, $division])
        <div class="alert alert-default">
            <i class="fa fa-users"></i>
            There {{ count($division->unassigned) === 1 ? 'is' : 'are' }} <code>{{ count($division->unassigned) }}</code> unassigned {{ Str::plural('member', count($division->unassigned)) }}  <strong>{{ $division->name }}</strong>. Drag them into a <a href="{{ route('division', $division->slug) }}/#platoons" class="alert-link">{{ $division->locality('platoon') }}</a> to assign.
        </div>
    @endcan
@endif

{{-- pending transfers --}}
@if (count($pendingTransfers = $division->transfers()->pending()->get()) > 0 && auth()->user()->isDivisionLeader())
        <div class="alert alert-default">
            <i class="fas fa-exchange-alt"></i>
            There {{ count($pendingTransfers) === 1 ? 'is' : 'are' }} <code>{{ count($pendingTransfers) }}</code>
            pending incoming {{ Str::plural('transfer', count($pendingTransfers)) }}.
            <a class="alert-link" href="{{ route('filament.mod.resources.transfers.index')}}?tableFilters[incomplete][isActive]=true&tableFilters[transferring_to][value]={{ $division->id }}">Manage incoming transfers</a>
        </div>
@endif