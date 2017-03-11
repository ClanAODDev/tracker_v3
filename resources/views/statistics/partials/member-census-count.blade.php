<div class="panel panel-filled">
    <div class="panel-heading">
        Clan Populations
    </div>
    <div class="panel-body">

        <h1>
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
            <span class="c-white">Total active members</span> in the Angels of Death clan. Percent difference from previous count of
            <code>{{ $previousCensus->count }}</code> on
            <code>{{ $previousCensus->date }}</code>. Census data is collected weekly.
        </div>

        <div class="m-t-md">
            <div class="row">
                <div class="col-md-12">
                    <div data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}"
                         data-weekly-active="{{ json_encode($lastYearCensus->pluck('weekly_active')) }}"
                         census-data></div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer slight">Last 30 weeks</div>
</div>
