<div class="census-table-container">
    <div class="census-table-header">
        <h4 class="census-table-title">
            <i class="fa fa-table"></i> Weekly Data
        </h4>
        <span class="census-table-count">{{ $censuses->count() }} weeks</span>
    </div>
    <div class="table-responsive">
        <table class="table census-table basic-datatable">
            <thead>
            <tr>
                <th>Date</th>
                <th class="text-center">Population</th>
                <th class="text-center no-sort">Change</th>
                <th class="text-center">Voice %</th>
                <th class="text-center no-sort">Voice Count</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($censuses as $index => $census)
                @php
                    $prev = $censuses->skip($index + 1)->first();
                    $popChange = $prev ? $census->count - $prev->count : 0;
                    $voicePercent = $census->count > 0
                        ? round($census->weekly_voice_count / $census->count * 100, 1)
                        : 0;
                @endphp
                <tr>
                    <td>
                        <span class="census-date">{{ $census->created_at->format('M j, Y') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="census-population">{{ $census->count }}</span>
                    </td>
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
                    <td class="text-center">
                        <span class="census-voice {{ $voicePercent >= 50 ? 'census-voice--good' : ($voicePercent >= 30 ? 'census-voice--okay' : 'census-voice--low') }}">
                            {{ $voicePercent }}%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="census-voice-count">{{ $census->weekly_voice_count }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
