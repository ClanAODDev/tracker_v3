@if (count($division->mismatchedTSMembers))
    <div class="alert alert-default">
        <i class="fa fa-exclamation-triangle text-danger"></i>
        You have <code>{{ count($division->mismatchedTSMembers) }}</code> {{ str_plural('member', count($division->mismatchedTSMembers)) }} improperly configured for Teamspeak. Please review the
        <a href="{{ route('division.ts-report', $division->abbreviation) }}"
           class="alert-link">Teamspeak Report</a> to resolve these issues.
    </div>
@endif

@if (! $division->handle)
    <div class="alert default">
        <i class="fa fa-gamepad text-warning"></i>
        <strong>{{ $division->name }}</strong> does not have a primary handle. Contact clan leadership to resolve this.
    </div>
@endif

@if (count($division->unassigned) > 0)
    {{-- only show notice if user is capable of taking action --}}
    @can('create', [App\Platoon::class, $division])
        <div class="alert alert-default">
            <i class="fa fa-users text-info"></i>
            There are <code>{{ count($division->unassigned) }}</code> unassigned members in
            <strong>{{ $division->name }}</strong>. Drag members into a
            <a href="{{ route('division', $division->abbreviation) }}/#platoons"
               class="alert-link">{{ $division->locality('platoon') }}</a> to assign them
        </div>
    @endcan
@endif
