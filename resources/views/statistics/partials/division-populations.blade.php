<table class="table table-striped table-hover basic-datatable">

    <thead>
    <tr>
        <th>Division</th>
        <th class="text-center">Population</th>
        <th class="text-center">Weekly Active</th>
        <th class="no-sort"></th>
    </tr>
    </thead>

    <tbody>
    @foreach ($divisionCensuses as $division)
        <tr>
            <td>{{ $division->name }}</td>
            <td class="text-center">{{ $division->census->first()->count }}</td>
            <td class="text-center">{{ $division->census->first()->weekly_active_count }}</td>
            <td class="text-center no-sort">
                <div class="census-pie"
                     data-counts="{{ json_encode([$division->census->first()->count, $division->census->first()->weekly_active_count]) }}"></div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

