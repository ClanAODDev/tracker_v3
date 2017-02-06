<div class="panel">
    <div class="panel-body">

        <h1 class="m-t-md m-b-xs" style="margin-top: 30px">
            <i class="pe pe-7s-global text-warning"> </i>

            {{ number_format($memberCount) }}

            @if($memberCount < $previousCensus->count)
                <span class="slight">
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
                    {{ percent($previousCensus->count, $memberCount) }}%
                </span>
            @else
                <span class="slight">
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
                    {{ percent($previousCensus->count, $memberCount) }}%
                </span>
            @endif

        </h1>

        <div class="small">
            <span class="c-white">Total active members</span> in the Angels of Death clan. Percent difference from
            previous count of {{ $previousCensus->count }} on {{ $previousCensus->date }}
        </div>

        <div class="m-t-sm">
            <div class="row">
                <div class="col-md-12">
                    <small class="c-white">
                    </small>
                    <div data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}"
                         census-data></div>
                    <div class="small text-center slight">Weekly census data</div>
                </div>
            </div>
        </div>
    </div>
</div>
