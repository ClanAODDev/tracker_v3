<div class="panel table-responsive">
    <div class="panel-heading">
        Weekly Census Data
        <span class="text-muted pull-right">{{ $previousCensus->date }}</span>
    </div>

    <table class="table table-hover basic-datatable">

        <thead>
        <tr>
            <th class="col-xs-3">Division</th>
            <th class="no-sort col-xs-3"></th>
            <th class="text-center col-xs-3">Population</th>
            <th class="text-center col-xs-3">Weekly Active</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($divisionCensuses as $division)
            <tr>
                <td>{{ $division->name }}</td>
                <td class="text-center no-sort">
                    <span class="census-pie"
                         data-counts="{{ json_encode([$division->popMinusActive, $division->weeklyActive]) }}">
                    </span>
                    <small class="slight">
                        {{ round($division->weeklyActive / $division->total * 100, 1) }}
                    </small>
                </td>
                <td class="text-center">{{ $division->census->last()->count}}</td>
                <td class="text-center">{{ $division->census->last()->weekly_active_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="panel-footer text-muted">
        <p>Populations reflect data collected during last census.</p>
    </div>
</div>

