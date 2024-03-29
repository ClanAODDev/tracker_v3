<div class="panel panel-filled">
    <div class="panel-heading">
        Census History
    </div>
    <div class="panel-body">
        <div class="flot-chart">
            <div class="flot-chart-content" id="flot-line-chart"
                 data-populations="{{ $populations }}"
{{--                 data-weekly-active="{{ $weeklyActive }}"--}}
                 data-weekly-ts="{{ $weeklyTsActive }}"
            ></div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="fas fa-dot-circle text-info"></i> - Division Population
            </div>
{{--            <div class="col-md-4 text-center">--}}
{{--                <i class="fas fa-dot-circle text-success"></i> - Weekly Active--}}
{{--            </div>--}}
            <div class="col-md-4 text-center">
                <i class="fas fa-dot-circle text-warning"></i> - Weekly Comms Active
            </div>
        </div>
    </div>
</div>

@section('footer_scripts')
    <script src="{!! asset('/js/census-graph.js?v=3') !!}"></script>
@endsection