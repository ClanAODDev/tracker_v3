<div class="panel panel-filled">
    <div class="panel-heading">
        Census History
        @if ($comments->count())
            <span class="pull-right">
                <label for="show-comments" class="slight">Show notes</label>
                <input type="checkbox" id="show-comments" value="comment" checked="checked" />
            </span>
        @endif
    </div>
    <div class="panel-body">
        <div class="flot-chart">
            <div class="flot-chart-content" id="flot-line-chart"
                 data-populations="{{ $populations }}"
                 data-weekly-active="{{ $weeklyActive }}"
                 data-weekly-ts="{{ $weeklyTsActive }}"
                 data-comments="{{ $comments }}"
            ></div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="row">
            <div class="col-md-4 text-center">
                <i class="fa fa-dot-circle-o text-info"></i> - Division Population
            </div>
            <div class="col-md-4 text-center">
                <i class="fa fa-dot-circle-o text-success"></i> - Weekly Active
            </div>
            <div class="col-md-4 text-center">
                <i class="fa fa-dot-circle-o text-warning"></i> - Weekly TS Active
            </div>
        </div>
    </div>
</div>

@section('footer_scripts')
    <script src="{!! asset('/js/census-graph.js?v=3') !!}"></script>
@endsection