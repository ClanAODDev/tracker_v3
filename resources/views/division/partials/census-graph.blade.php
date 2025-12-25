<div class="census-chart-container">
    <div class="census-chart-header">
        <h4 class="census-chart-title">
            <i class="fa fa-chart-line"></i> Census History
        </h4>
        <div class="census-chart-legend">
            <span class="census-legend-item">
                <span class="census-legend-dot census-legend-dot--population"></span>
                Population
            </span>
            <span class="census-legend-item">
                <span class="census-legend-dot census-legend-dot--voice"></span>
                Weekly Voice Active
            </span>
        </div>
    </div>
    <div class="census-chart-body">
        <div class="flot-chart">
            <div class="flot-chart-content" id="flot-line-chart"
                 data-populations="{{ $populations }}"
                 data-weekly-discord="{{ $weeklyDiscordActive }}"
            ></div>
        </div>
    </div>
</div>

@section('footer_scripts')
    @vite(['resources/assets/js/census-graph.js'])
@endsection
