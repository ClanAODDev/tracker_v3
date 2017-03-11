<div class="panel panel-filled table-responsive">
    <table class="table table-hover basic-datatable">

        <thead>
        <tr>
            <th class="col-xs-3">Division</th>
            <th class="text-center col-xs-3">Population</th>
            <th class="text-center col-xs-3">Weekly Active</th>
            <th class="no-sort col-xs-3"></th>
        </tr>
        </thead>

        <tbody>
        @foreach ($divisionCensuses as $division)
            <tr>
                <td class="col-xs-3">{{ $division->name }}</td>
                <td class="text-center col-xs-3">{{ $division->census->first()->count }}</td>
                <td class="text-center col-xs-3">{{ $division->census->first()->weekly_active_count }}</td>
                <td class="text-center no-sort col-xs-3">
                    <div class="census-pie"
                         data-counts="{{ json_encode([$division->census->first()->count, $division->census->first()->weekly_active_count]) }}"></div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

