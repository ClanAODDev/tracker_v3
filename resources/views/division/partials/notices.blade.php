@if ($division->isShutdown())
    <div class="alert alert-danger">
        @if ($division->shut_down_at > now())
            <i class="fa fa-exclamation-circle"></i> This division was shut down on
            <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should find new divisions
            or be removed immediately.
        @else
            <i class="fa fa-exclamation-circle"></i> This division will be shut down on
            <strong>{{ $division->shutdown_at->format('Y-m-d') }}</strong>. Assigned members should begin looking for
            new divisions and begin transferring.
        @endif
    </div>
@endif

@can('manage', \App\Models\MemberRequest::class)
    @if ($count = $division->memberRequests()->cancelled()->count())
        <div class="alert alert-warning">
            There are <code>{{ $count }}</code> {{ Str::plural('member request', $count) }} in need of attention. <a
                    class="alert-link pull-right"
                    href="{{ route('admin.member-request.index') }}"
            >Manage Member Requests</a>
        </div>
    @elseif($count = $division->memberRequests()->pending()->count())
        <div class="alert alert-warning">
            There are <code>{{ $count }}</code> {{ Str::plural('member request', $count) }} pending.
            <a class="alert-link pull-right"
               href="{{ route('admin.member-request.index') }}"
            >Manage Member Requests</a>
        </div>
    @endif
@endcan

@if($division->outstandingInactives && auth()->user()->isRole('sr_ldr'))
    <div class="alert alert-default">
        There are
        <code>{{ $division->outstandingInactives }}</code> outstanding
        inactive {{ Str::plural('member', $division->outstandingInactives) }}. AOD does not allow divisions to maintain
        members whose last activity exceeds <code>{{ config('app.aod.maximum_days_inactive') }}</code> days
        except where a leave of absence exists. Please
        <a href="{{ route('division.inactive-members', $division) }}">process these members</a> out of AOD.
    </div>
@endif

@if($division->outstandingAwardRequests && auth()->user()->isDivisionLeader())
    <div class="alert alert-default">
        <i class="fa fa-trophy fa-lg c-white"></i> Pending award requests for approval.

        <a href="{{ route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id) }}">Manage Requests</a>
    </div>
@endif

@if ($division->members()->misconfiguredDiscord()->count() && auth()->user()->isRole(['sr_ldr', 'jr_ldr']))
    <div class="alert alert-default">
        There are assigned members with voice comms issues. Please review the
        <a href="{{ route('division.voice-report', $division->slug) }}">Voice Comms Report</a>.
    </div>
@endif

@if (! $division->handle)
    <div class="alert alert-default">
        Your division does not have a primary handle. Contact clan leadership to resolve this.
    </div>
@endif

@if (count($division->unassigned) > 0)
    {{-- only show notice if user is capable of taking action --}}
    @can('create', [App\Models\Platoon::class, $division])
        <div class="alert alert-default">
            <i class="fa fa-users text-info"></i>
            There are
            <code>{{ count($division->unassigned) }}</code>
            unassigned {{ Str::plural('member', count($division->unassigned)) }} in
            <strong>{{ $division->name }}</strong>. Drag members into a
            <a href="{{ route('division', $division->slug) }}/#platoons"
               class="alert-link">{{ $division->locality('platoon') }}</a> to assign them
        </div>
    @endcan
@endif
