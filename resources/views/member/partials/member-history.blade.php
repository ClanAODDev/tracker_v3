@if($transfers->count() > 1)
    @php
        $sortedTransfers = $transfers->sortBy('created_at')->values();
        $pastCount = $sortedTransfers->count() - 1;

        $history = collect();
        for ($i = 0; $i < $pastCount; $i++) {
            $transfer = $sortedTransfers->get($i);
            $next     = $sortedTransfers->get($i + 1);
            $history->push([
                'division'   => $transfer->division,
                'days'       => $transfer->created_at->diffInDays($next->created_at),
                'started_at' => $transfer->created_at,
            ]);
        }

        $grouped = $history
            ->groupBy(fn ($item) => $item['division']->id)
            ->map(function ($items) {
                $totalDays = $items->sum('days');
                $years     = (int) ($totalDays / 365);
                $months    = (int) (($totalDays % 365) / 30);
                $duration  = trim(($years ? "{$years}y " : '') . ($months ? "{$months}m" : '')) ?: '<1m';
                return [
                    'division'   => $items->first()['division'],
                    'duration'   => $duration,
                    'total_days' => $totalDays,
                    'visits'     => $items->count(),
                    'last_seen'  => $items->max('started_at'),
                ];
            })
            ->sortByDesc('total_days')
            ->values();
    @endphp

    <h4 class="m-t-xl">Past Divisions</h4>
    <hr/>
    <div class="division-chips">
        @foreach($grouped as $item)
            <a href="{{ route('division', $item['division']->slug) }}" class="division-chip">
                <img src="{{ $item['division']->getLogoPath() }}" alt="{{ $item['division']->name }}" class="division-chip-icon">
                <span class="division-chip-name">{{ $item['division']->name }}</span>
                <span class="division-chip-date">{{ $item['duration'] }}</span>
                @if($item['visits'] > 1)
                    <span class="division-chip-visits">×{{ $item['visits'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
@endif
