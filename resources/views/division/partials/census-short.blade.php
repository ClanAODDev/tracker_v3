<h1>
    <i class="pe pe-7s-joy text-warning"> </i>

    {{ number_format($division->activeMembers->count()) }}

    @if($division->activeMembers->count() < $previousCensus->count)
        <span class="slight">
                    <i class="fa fa-play fa-rotate-90 c-white"></i>
            {{ percent($previousCensus->count, $division->activeMembers->count()) }}%
                </span>
    @else
        <span class="slight">
                    <i class="fa fa-play fa-rotate-270 text-warning"></i>
            {{ percent($previousCensus->count, $division->activeMembers->count()) }}%
                </span>
    @endif

    <a href="#" class="btn btn-default pull-right">
        View <span class="hidden-sm hidden-xs">Census Data</span>
    </a>
</h1>

<div class="small">
    <span class="c-white">Total active members</span> in the {{ $division->name }} Division. Percent difference from previous count of
    <strong>{{ $previousCensus->count }}</strong> on
    <strong>{{ $previousCensus->date }}</strong>. Census data is collected weekly.
</div>

<div class="m-t-md">
    <div class="row">
        <div class="col-md-12">
            <div data-counts="{{ json_encode($lastYearCensus->pluck('count')) }}" census-data></div>
        </div>
    </div>
</div>




{{--

flot stuff

<script>
    $(document).ready(function () {
        // Flot charts data and options
        var data1 = [[0, 16], [1, 24], [2, 11], [3, 7], [4, 10], [5, 15], [6, 24], [7, 30]];
        var data2 = [[0, 76], [1, 44], [2, 31], [3, 27], [4, 36], [5, 46], [6, 56], [7, 66]];

        var chartUsersOptions = {
            series: {
                splines: {
                    show: true,
                    tension: 0.4,
                    lineWidth: 1,
                    fill: 1

                }

            },
            grid: {
                tickColor: "#404652",
                borderWidth: 1,
                borderColor: '#404652',
                color: '#404652'
            },
            colors: ["#f7af3e", "#DE9536"]
        };

        $.plot($("#flot-line-chart"), [data2, data1], chartUsersOptions);
    });
</script>
--}}
