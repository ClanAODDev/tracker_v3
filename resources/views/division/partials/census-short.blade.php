<div class="division-section animate-fade-in-up" style="animation-delay: 0.2s">
    <h3 class="division-section-title">
        Population Trend
        @if ($previousCensus)
            <span class="text-muted" style="font-size: 10px; letter-spacing: 0; font-weight: 400; margin-left: 8px;">
                prev. {{ $previousCensus->count }} members &middot; {{ $previousCensus->date }}
            </span>
        @endif
    </h3>
    <hr/>
    <div class="census-sparkline-container">
        <div data-counts="{{ json_encode($chartData['population']) }}"
             data-weekly-voice="{{ json_encode($chartData['voiceActive']) }}"
             census-data></div>
        <div class="census-legend">
            <span class="legend-item"><span class="legend-line legend-population"></span> Members</span>
            <span class="legend-item"><span class="legend-line legend-voice"></span> Voice Active</span>
        </div>
    </div>
</div>