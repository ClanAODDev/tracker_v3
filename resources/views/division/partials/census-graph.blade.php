<div class="panel panel-filled">
    <div class="panel-heading">
        Census History
    </div>
    <div class="panel-body">
        <div class="flot-chart">
            <div class="flot-chart-content" id="flot-line-chart"
                 data-populations="{{ $populations }}"
                 data-weekly-discord="{{ $weeklyDiscordActive }}"
            ></div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-6 text-center">
                <i class="fas fa-dot-circle text-success"></i> Division Population
            </div>
            <div class="col-md-6 text-center">
                <i class="fas fa-dot-circle text-warning"></i> Discord
                <p><small class="text-muted"># active VoIP past week</small></p>
            </div>
        </div>
    </div>
</div>

@section('footer_scripts')
    <script src="{!! asset('/js/census-graph.js?v=3.2') !!}"></script>
@endsection