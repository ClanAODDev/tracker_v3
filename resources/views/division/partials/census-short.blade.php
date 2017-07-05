<h1>
    <i class="pe pe-7s-joy text-warning"> </i>

    {{ number_format($division->members->count()) }}

    @if($previousCensus)
        @if($division->members->count() < $previousCensus->count)
            <span class="slight">
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
                {{ percent($previousCensus->count, $division->members->count()) }}%
                </span>
        @else
            <span class="slight">
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
                {{ percent($previousCensus->count, $division->members->count()) }}%
                </span>
        @endif
    @endif
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