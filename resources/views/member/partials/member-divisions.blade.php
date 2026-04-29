@php
    $hasActiveDivision = $division && $member->division_id != 0 && !$member->isPending;
    $hasPastDivisions  = $transfers->count() > 1;

    if ($hasPastDivisions) {
        $sortedTransfers = $transfers->sortBy('created_at')->values();
        $pastCount       = $sortedTransfers->count() - 1;

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

        $pastGrouped = $history
            ->groupBy(fn ($item) => $item['division']->id)
            ->filter(fn ($items) => !$hasActiveDivision || $items->first()['division']->id !== $division->id)
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
                ];
            })
            ->sortByDesc('total_days')
            ->values();
    } else {
        $pastGrouped = collect();
    }

    $currentDivisionSince = $hasActiveDivision
        ? $transfers->sortByDesc('created_at')->first()?->created_at
        : null;

    $showSection = $hasActiveDivision || $partTimeDivisions->count() > 0;
@endphp

@if($showSection)
    <h4 class="m-t-xl">
        Divisions
        @can('managePartTime', $member)
            @if($member->id === auth()->user()->member_id)
                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#part-time-divisions-modal">
                    <i class="fa fa-cog"></i> Manage
                </button>
            @else
                <a href="{{ route('filament.mod.resources.members.edit', $member) }}#part-time-divisions"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @endif
        @endcan
    </h4>
    <hr/>

    @if($hasActiveDivision || $partTimeDivisions->count() > 0)
        <div class="division-cards">
            @if($hasActiveDivision)
                <a href="{{ route('division', $division->slug) }}" class="division-card division-card--primary">
                    <div class="division-card-logo">
                        <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}">
                    </div>
                    <div class="division-card-info">
                        <div class="division-card-name">{{ $division->name }}</div>
                        <div class="division-card-meta">
                            <span class="division-card-badge">Primary</span>
                            @if($currentDivisionSince)
                                <span class="division-card-since">Since {{ $currentDivisionSince->format('M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endif

            @foreach($partTimeDivisions as $ptDiv)
                <a href="{{ route('division', $ptDiv->slug) }}" class="division-card">
                    <div class="division-card-logo">
                        <img src="{{ $ptDiv->getLogoPath() }}" alt="{{ $ptDiv->name }}">
                    </div>
                    <div class="division-card-info">
                        <div class="division-card-name">{{ $ptDiv->name }}</div>
                        <div class="division-card-meta">
                            <span class="division-card-badge division-card-badge--secondary">Part-Time</span>
                            <span class="division-card-since">Since {{ $ptDiv->pivot->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    @endif

    @if($pastGrouped->count() > 0)
        <div class="division-section-label">Past</div>
        <div class="division-chips">
            @foreach($pastGrouped as $item)
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
@endif
