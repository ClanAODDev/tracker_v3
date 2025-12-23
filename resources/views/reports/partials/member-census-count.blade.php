@php
    $percentChange = $memberCount > 0
        ? abs(round((1 - $previousCensus->count / $memberCount) * 100, 2))
        : 0;
    $isGrowth = $memberCount >= $previousCensus->count;
@endphp

<div class="panel panel-filled">
    <div class="panel-body">
        <h1>
            <i class="pe pe-7s-global text-warning"></i>
            {{ number_format($memberCount) }}
            <div class="slight" style="display: inline-block">
                @if($isGrowth)
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
                @else
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
                @endif
                {{ $percentChange }}%
            </div>
        </h1>

        <div class="small">
            <span class="c-white">Total active members</span> in AOD. Percent difference from previous count of
            <code>{{ number_format($previousCensus->count) }}</code> on
            <code>{{ $previousCensus->date }}</code>.
        </div>
    </div>

    <div class="sparkline"
         data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}"
         data-weekly-voice="{{ json_encode($lastYearCensus->pluck('weekly_voice_active')) }}"
         census-data>
    </div>

    <div class="panel-footer" style="padding: 5px 15px;">
        <small class="text-muted">
            <span style="color: #fff;">&#9644;</span> Population
            <span style="color: #56C0E0; margin-left: 10px;">&#9644;</span> Weekly Discord
        </small>
    </div>
</div>
