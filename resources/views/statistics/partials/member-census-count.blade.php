<div class="panel panel-filled">

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
            <span class="c-white">Total active members</span> in AOD. Percent difference from previous count of
            <code>{{ $previousCensus->count }}</code> on
            <code>{{ $previousCensus->date }}</code>.
        </div>
    </div>

    <div class="sparkline" data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}"
         data-weekly-active="{{ json_encode($lastYearCensus->pluck('weekly_active')) }}"
         census-data>
    </div>

</div>
