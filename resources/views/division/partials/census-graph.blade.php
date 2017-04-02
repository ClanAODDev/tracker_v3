<div class="panel panel-filled">
    <div class="panel-heading">
        Census History
        <span class="pull-right">
            <label for="show-comments" class="slight">Show notes</label>
            <input type="checkbox" id="show-comments" value="comment" checked="checked" />
        </span>
    </div>
    <div class="panel-body">
        <div class="flot-chart">
            <div class="flot-chart-content" id="flot-line-chart"
                 data-populations="{{ $populations }}"
                 data-weekly-active="{{ $weeklyActive }}"
                 data-comments="{{ $comments }}"
            ></div>
        </div>
    </div>
</div>

@section('footer_scripts')
    <script src="{!! asset('/js/census-graph.js') !!}"></script>
@endsection