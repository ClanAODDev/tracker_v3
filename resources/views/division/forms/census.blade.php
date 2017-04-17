<div class="table-responsive">
    <table class="table table-hover basic-datatable table-striped">
        <thead>
        <tr>
            <th>Date</th>
            <th class="no-sort"></th>
            <th class="text-center">Population</th>
            <th class="text-center">Weekly Active</th>
            <th>Notes</th>
        </tr>
        </thead>
        @foreach ($censuses as $census)

            @php
                $popMinus = $census->count - $census->weekly_active_count;
            @endphp

            <tr>
                <td>{{ $census->created_at->format('m/d/Y') }}</td>
                <td class="text-center no-sort">
                    <span class="census-pie"
                          data-counts="{{ json_encode([$popMinus, $census->weekly_active_count]) }}">
                    </span>
                    <small class="slight">
                        {{ round($census->weekly_active_count / $census->count * 100, 1) }}%
                    </small>
                </td>
                <td class="text-center">{{ $census->count }}</td>
                <td class="text-center">{{ $census->weekly_active_count }}</td>
                <td>{{ $census->notes }}</td>
            </tr>
        @endforeach
    </table>
</div>