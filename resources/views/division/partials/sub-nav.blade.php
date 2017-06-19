<div class="row m-b-xl">
    <div class="col-xs-12">
        <a href="{{ route('partTimers', $division->abbreviation) }}" class="btn btn-default">
            Part Timers
        </a>
        <a href="{{ route('division.census', $division->abbreviation) }}" class="btn btn-default ">
            Census Data
        </a>
        <a href="{{ route('division.structure', $division->abbreviation) }}" class="btn btn-default ">
            Structure
        </a>
    </div>
</div>
