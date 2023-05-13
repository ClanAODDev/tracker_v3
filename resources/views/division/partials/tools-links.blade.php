<div class="dropdown" style="display: inline-block;">
    <button class="btn btn-default dropdown-toggle" type="button" id="tools" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> Tools
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="tools">

        @can ('create', App\Models\Member::class)
            @if (!$division->isShutdown())
                <li>
                    <a href="{{ route('recruiting.form', $division) }}">Add Recruit</a>
                </li>
                <li role="separator" class="divider"></li>
            @endif
        @endcan

        <li>
            <a href="{{ route('partTimers', $division) }}">
                Part Timers
            </a>
        </li>
        <li>
            @can ('viewDivisionStructure', auth()->user())
                <a href="{{ route('division.structure', $division) }}">
                    Division Structure
                </a>
            @endcan
        </li>
        <li>
            <a href="{{ route('division.inactive-members', $division) }}">Inactives</a>
        </li>
        <li>
            <a href="{{ route('leave.index', $division) }}">
                Leaves of Absence
            </a>
        </li>

        @can ('show', App\Models\Note::class)
            <li>
                <a href="{{ route('division.notes', $division) }}">Member Notes</a>
            </li>
        @endcan

            @can('viewForDivision', [App\Models\Recommendation::class, $division->id])
                <li>
                    <a href="{{ route('division.recommendations', $division) }}">Recommendations</a>
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
            <a href="{{ route('division.promotions', $division) }}">Promotions</a>
        </li>

        <li>
            <a href="{{ route('division.retention-report', $division) }}">Member Retention</a>
        </li>

        <?php $file = Str::camel($division->name); ?>
        @if (file_exists(resource_path("views/division/reports/ingame-reports/{$file}.blade.php")))
            <li>
                <a href="{{ route('division.ingame-reports', $division) }}">Ingame Report</a>
            </li>
        @endif
    </ul>
</div>

<a href="{{ route('division.members', $division) }}" class="btn btn-default">
    <i class="fa fa-users text-accent"></i> Members
</a>

@can('manage', \App\Models\MemberRequest::class)
    <a href="{{ route('admin.member-request.index') }}" class="btn btn-default">
        <i class="fa fa-users text-accent"></i> Member Requests
    </a>
@endcan