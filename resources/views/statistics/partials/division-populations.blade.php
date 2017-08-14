<div class="panel table-responsive">
    <div class="panel-heading">
        Weekly Census Data
        <span class="text-muted pull-right">{{ $previousCensus->date }}</span>
    </div>

    <table class="table table-hover basic-datatable">

        <thead>
        <tr>
            <th class="col-xs-4">Division</th>
            <th class="text-center col-xs-2">Population</th>
            <th class="text-center col-xs-2">Weekly Active</th>
            <th class="text-center col-xs-2">Weekly TS Active</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($censuses as $division)
            <tr>
                <td>
                    <a href="{{ route('division.census', $division->abbreviation) }}">
                        <i class="fa fa-search"></i>
                    </a>
                    {{ $division->name }}
                </td>
                <td class="text-center">{{ $division->census->last()->count}}</td>
                <td class="text-center">
                    {{ $division->census->last()->weekly_active_count }}
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#1bbf89']) }}"
                          data-counts="{{ json_encode([$division->popMinusActive, $division->weeklyActive]) }}">
                    </span>
                </td>
                <td class="text-center">
                    {{ $division->census->last()->weekly_ts_count }}
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#56C0E0']) }}"
                          data-counts="{{ json_encode([$division->popMinusActive, $division->weeklyTsActive]) }}">
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="panel-footer text-muted">
        <p>Populations reflect data collected during last census.</p>
    </div>
</div>

