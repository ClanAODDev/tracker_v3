<div class="dropdown m-b-sm" style="display: inline-block;">
    <button class="btn btn-default dropdown-toggle" type="button" id="tools" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> Tools
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="tools">

        @can('recruit', App\Models\Member::class)
            @if (!$division->isShutdown())
                <li>
                    <a href="{{ route('recruiting.form', $division) }}">Add Recruit</a>
                </li>
                <li role="separator" class="divider"></li>
            @endif
        @endcan

        <li>
            <a href="{{ route('partTimers', $division) }}">
                Manage Part Timers
            </a>
        </li>
        <li>
            @can ('viewDivisionStructure', auth()->user())
                <a href="{{ route('division.structure', $division) }}">
                    Generate Structure
                </a>
            @endcan
        </li>
        <li>
            <a href="{{ route('division.inactive-members', $division) }}">Manage Inactives</a>
        </li>
        @can('create', \App\Models\Leave::class)
            <li>
                <a href="{{ route('filament.mod.resources.leaves.index') }}">
                    Manage Leaves of Absence
                </a>
            </li>
        @endcan

        @can ('show', App\Models\Note::class)
            <li>
                <a href="{{ route('division.notes', $division) }}">View Notes</a>
            </li>
        @endcan

    </ul>
</div>

<div class="dropdown" style="display: inline-block;">
    <button class="btn btn-default dropdown-toggle" type="button" id="reports" data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-paperclip text-accent"></i> Reports
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="reports">
        <li>
            <a href="{{ route('division.census', $division) }}">
                Census
            </a>
        </li>

        <li>
            <a href="{{ route('division.retention-report', $division) }}">Member Retention</a>
        </li>


        <li>
            <a href="{{ route('division.promotions', $division) }}">Promotions</a>
        </li>

        <li>
            <a href="{{ route('division.voice-report', $division) }}">Voice Comms Issues</a>
        </li>
    </ul>
</div>

<a href="{{ route('division.members', $division) }}" class="btn btn-default">
    <i class="fa fa-users text-accent"></i> Members
</a>

@can('manage', \App\Models\MemberRequest::class)
    <a href="{{ route('filament.mod.resources.member-requests.index') }}" class="btn btn-default">
        <i class="fa fa-users text-accent"></i> Member Requests
    </a>
@endcan

<a href="{{ route('awards.index', ['division' => $division->slug]) }}" class="btn btn-default">
    <i class="fa fa-trophy text-accent"></i> Awards
</a>