<div class="btn-group">
    @can ('create', App\Member::class)
        <a href="{{ route('recruiting.form', [$division->abbreviation]) }}"
           class=" btn btn-default m-t-sm ">Add Recruit</a>
    @endcan
    <a href="{{ route('partTimers', $division->abbreviation) }}"
       class=" btn btn-default m-t-sm ">
        Part Timers
    </a>
    @can ('viewDivisionStructure', auth()->user())
        <a href="{{ route('division.structure', $division->abbreviation) }}"
           class=" btn btn-default m-t-sm ">
            Structure
        </a>
    @endcan

    <a href="{{ route('leave.index', $division->abbreviation) }}"
       class=" btn btn-default m-t-sm ">
        Leave
    </a>

    <a href="{{ route('division.inactive-members', $division->abbreviation) }}"
       class=" btn btn-default m-t-sm ">
        Inactive Members
    </a>
</div>