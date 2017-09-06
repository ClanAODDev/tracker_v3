<h1>
    <i class="pe pe-7s-users text-warning"> </i>

    {{ number_format($division->members->count()) }}



    @if($previousCensus)
        <div class="slight" style="display: inline-block">
            @if($division->members->count() < $previousCensus->count)
                <i class="fa fa-play fa-rotate-90 c-white"></i>
            @else
                <i class="fa fa-play fa-rotate-270 text-warning"></i>
            @endif
            {{ abs(number_format((1 - $previousCensus->count / $division->members->count()) * 100, 2)) }}%
        </div>
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