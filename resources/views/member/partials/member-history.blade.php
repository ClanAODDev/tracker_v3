@if($transfers->count() > 1)
    <h4 class="m-t-xl">Past Divisions</h4>
    <hr/>
    <div class="division-chips">
        @php
            $sortedTransfers = $transfers->sortBy('created_at')->values();
        @endphp
        @foreach($sortedTransfers->slice(0, -1) as $index => $transfer)
            @php
                $nextTransfer = $sortedTransfers->get($index + 1);
                $duration = $transfer->created_at->diff($nextTransfer->created_at);
                $durationText = '';
                if ($duration->y > 0) $durationText .= $duration->y . 'y ';
                if ($duration->m > 0) $durationText .= $duration->m . 'm';
                if (!$durationText) $durationText = '<1m';
            @endphp
            <a href="{{ route('division', $transfer->division) }}" class="division-chip">
                <img src="{{ $transfer->division->getLogoPath() }}" alt="{{ $transfer->division->name }}" class="division-chip-icon">
                <span class="division-chip-name">{{ $transfer->division->name }}</span>
                <span class="division-chip-date">{{ trim($durationText) }}</span>
            </a>
        @endforeach
    </div>
@endif
