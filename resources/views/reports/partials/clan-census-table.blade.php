<div class="panel table-responsive" style="margin-bottom: 20px;">
    <div class="panel-heading">
        Census History
        <span class="text-muted pull-right">{{ $filteredCensus->count() }} weeks</span>
    </div>

    <table class="table table-hover basic-datatable">
        <thead>
        <tr>
            <th>Date</th>
            <th class="text-center">Population</th>
            <th class="text-center no-sort">Change</th>
            <th class="text-center">Voice Active</th>
            <th class="text-center">Voice %</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($filteredCensus as $index => $row)
            @php
                $prev = $filteredCensus->get($index - 1);
                $popChange = $prev ? (int) $row->count - (int) $prev->count : 0;
                $voicePercent = (int) $row->count > 0
                    ? round((int) $row->weekly_voice_active / (int) $row->count * 100, 1)
                    : 0;
            @endphp
            <tr>
                <td data-order="{{ \Carbon\Carbon::parse($row->date)->timestamp }}">
                    {{ \Carbon\Carbon::parse($row->date)->format('M j, Y') }}
                </td>
                <td class="text-center">{{ number_format((int) $row->count) }}</td>
                <td class="text-center">
                    @if($popChange !== 0)
                        <span class="census-change {{ $popChange > 0 ? 'census-change--up' : 'census-change--down' }}">
                            <i class="fa fa-{{ $popChange > 0 ? 'caret-up' : 'caret-down' }}"></i>
                            {{ abs($popChange) }}
                        </span>
                    @else
                        <span class="census-change census-change--neutral">—</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format((int) $row->weekly_voice_active) }}</td>
                <td class="text-center">
                    <span class="census-voice {{ $voicePercent >= 50 ? 'census-voice--good' : ($voicePercent >= 30 ? 'census-voice--okay' : 'census-voice--low') }}">
                        {{ $voicePercent }}%
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
