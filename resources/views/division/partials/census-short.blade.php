<div class="m-t-md">
    <div class="row">
        <div class="col-md-12">
            <div class="census-sparkline-container">
                <div data-counts="{{ json_encode($chartData['population']) }}"
                     data-weekly-voice="{{ json_encode($chartData['voiceActive']) }}"
                     census-data></div>
                <div class="census-legend">
                    <span class="legend-item"><span class="legend-line legend-population"></span> Members</span>
                    <span class="legend-item"><span class="legend-line legend-voice"></span> Voice Active</span>
                </div>
            </div>
            @if ($previousCensus)
                <p class="small text-muted m-t-sm">
                    Weekly census data. Previous: <strong>{{ $previousCensus->count }}</strong> members on <strong>{{ $previousCensus->date }}</strong>
                </p>
            @endif
        </div>
    </div>
</div>