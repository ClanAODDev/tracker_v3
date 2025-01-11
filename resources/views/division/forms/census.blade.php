<div class="table-responsive">
    <table class="table table-hover basic-datatable table-striped">
        <thead>
        <tr>
            <th>Date</th>
            <th class="text-center">Population</th>
            <th class="text-center" title="Data began collection 4/7/2024">Discord*</th>
            {{--            <th class="text-center">Weekly Forum Active</th>--}}
            {{--<th>Notes</th>--}}
        </tr>
        </thead>
        @foreach ($censuses as $census)

            @php
                $popMinusDiscord = $census->count - $census->weekly_voice_count;
            @endphp

            <tr>
                <td>{{ $census->created_at->format('m/d/Y') }}</td>
                <td class="text-center">{{ $census->count }}</td>
                <td class="text-center slight">
                    {{ $census->count > 0 ? number_format($census->weekly_voice_count / $census->count * 100, 1) : 0
                     }}%
                    <span class="census-pie"
                          data-colors="{{ json_encode(['#404652', '#56C0E0']) }}"
                          data-counts="{{ json_encode([$popMinusDiscord, $census->weekly_voice_count]) }}">
                    </span>
                </td>
            </tr>
        @endforeach
    </table>
</div>