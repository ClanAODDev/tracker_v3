@if ($count = $division->memberRequests()->cancelled()->count())
    <div class="alert alert-warning">
        You have <code>{{ $count }}</code> {{ str_plural('member request', $count) }} in need of attention. <a class="alert-link pull-right"
                href="{{ route('division.member-requests.index', $division) }}">Manage Member Requests</a>
    </div>
@endif

@if($division->outstandingInactives)
    <div class="alert alert-default">
        You have
        <code>{{ $division->outstandingInactives }}</code> outstanding inactive {{ str_plural('member', $division->outstandingInactives) }}. AOD does not allow divisions to maintain members whose last forum activity exceeds
        <code>{{ config('app.aod.maximum_days_inactive') }}</code> days except where a leave of absence exists. Please
        <a href="{{ route('division.inactive-members', $division) }}">process these members</a> out of AOD.
    </div>
@endif

@if (count($division->mismatchedTSMembers))
    <div class="alert alert-default">
        You have
        <code>{{ count($division->mismatchedTSMembers) }}</code> {{ str_plural('member', count($division->mismatchedTSMembers)) }} improperly configured for Teamspeak. Please review the
        <a href="{{ route('division.ts-report', $division->abbreviation) }}">Teamspeak Report</a> to resolve these issues.
    </div>
@endif

@if (! $division->handle)
    <div class="alert alert-default">
        Your division does not have a primary handle. Contact clan leadership to resolve this.
    </div>
@endif

@if (count($division->unassigned) > 0)
    {{-- only show notice if user is capable of taking action --}}
    @can('create', [App\Platoon::class, $division])
        <div class="alert alert-default">
            <i class="fa fa-users text-info"></i>
            You have
            <code>{{ count($division->unassigned) }}</code> unassigned {{ str_plural('member', count($division->unassigned)) }} in
            <strong>{{ $division->name }}</strong>. Drag members into a
            <a href="{{ route('division', $division->abbreviation) }}/#platoons"
               class="alert-link">{{ $division->locality('platoon') }}</a> to assign them
        </div>
    @endcan
@endif
