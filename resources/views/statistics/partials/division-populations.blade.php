<div class="panel table-responsive">
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
                    <div class="census-pie" data-counts="{{ json_encode([$division->popMinusActive, $division->weeklyActive]) }}"></div>
                </td>
                <td class="text-center">{{ $division->census->first()->count }}</td>
                <td class="text-center">{{ $division->census->first()->weekly_active_count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

