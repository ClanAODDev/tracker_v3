<div class="row m-b-xl">
    <div class="col-xs-12">
        <a href="{{ route('partTimers', $division->slug) }}" class="btn btn-default">
            Part Timers
        </a>
        <a href="{{ route('division.census', $division->slug) }}" class="btn btn-default ">
            Census Data
        </a>
        <a href="{{ route('division.structure', $division->slug) }}" class="btn btn-default ">
            Structure
        </a>
        <a href="{{ route('leave.index', $division->slug) }}" class="btn btn-default ">
            Leave
        </a>
        <a href="{{ route('division.inactive-members', $division->slug) }}" class="btn btn-default ">
            Inactive Members
        </a>
    </div>
</div>
