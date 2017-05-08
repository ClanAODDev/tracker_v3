<h1>
    <i class="pe pe-7s-joy text-warning"> </i>

    {{ number_format($division->activeMembers->count()) }}

    @if($previousCensus)
        @if($division->activeMembers->count() < $previousCensus->count)
            <span class="slight">
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
                {{ percent($previousCensus->count, $division->activeMembers->count()) }}%
                </span>
        @else
            <span class="slight">
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
                {{ percent($previousCensus->count, $division->activeMembers->count()) }}%
                </span>
        @endif
    @endif

    <a href="{{ route('division.census', $division->abbreviation) }}" class="btn btn-default pull-right">
        View <span class="hidden-sm hidden-xs">Census Data</span>
    </a>
</h1>

<div class="small">
    <span class="c-white">Total active members</span> in the {{ $division->name }} Division.
    @if ($previousCensus)
        Percent difference from previous count of
        <strong>{{ $previousCensus->count }}</strong> on
        <strong>{{ $previousCensus->date }}</strong>. Census data is collected weekly.
    @endif
</div>

<div class="m-t-md">
    <div class="row">
        <div class="col-md-12">
            <div data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}" census-data></div>
        </div>
    </div>
</div>