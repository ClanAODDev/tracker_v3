<div class="dropdown" style="display: inline-block;">
    <button class="btn btn-default dropdown-toggle" type="button" id="tools" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> Tools
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="tools">

        @can ('create', App\Member::class)
            <li>
                <a href="{{ route('recruiting.form', [$division->abbreviation]) }}">Add Recruit</a>
            </li>
            <li role="separator" class="divider"></li>
        @endcan

        <li>
            <a href="{{ route('partTimers', $division->abbreviation) }}">
                Manage Part Timers
            </a>
        </li>
        <li>
            @can ('viewDivisionStructure', auth()->user())
                <a href="{{ route('division.structure', $division->abbreviation) }}">
                    Generate Structure
                </a>
            @endcan
        </li>
        <li>
            <a href="{{ route('division.inactive-members', $division->abbreviation) }}">Manage Inactives</a>
        </li>
        <li>
            <a href="{{ route('leave.index', $division->abbreviation) }}">
                Manage Leaves of Absence
            </a>
        </li>

    </ul>
</div>

<div class="dropdown"  style="display: inline-block;">
    <button class="btn btn-default dropdown-toggle" type="button" id="reports" data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="true">
        <i class="fa fa-paperclip text-accent"></i> Reports
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="reports">
        <li>
            <a href="{{ route('division.census', $division->abbreviation) }}">
                Census
            </a>
        </li>
        <li>
            <a href="{{ route('division.promotions', $division->abbreviation) }}">Promotions</a>
        </li>
    </ul>
</div>

<a href="{{ route('division.members', $division->abbreviation) }}" class="btn btn-default">
    <i class="fa fa-users text-accent"></i> Members
</a>