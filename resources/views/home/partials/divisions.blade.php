@php
    $divisionStats = [];
    foreach ($leaderboard->voiceLeaders as $entry) {
        $divisionStats[$entry['id']]['voice'] = $entry['formatted'];
    }
    foreach ($leaderboard->recruitLeaders as $entry) {
        $divisionStats[$entry['id']]['recruits'] = $entry['value'];
    }
@endphp
<div class="divisions-grid">
    @foreach($divisions as $division)
        @php $stats = $divisionStats[$division->id] ?? []; @endphp
        <a href="{{ route('division', $division->slug) }}" class="division-card animate-scale-in animate-stagger {{ $division->isShutdown() ? 'division-card-shutdown' : '' }}" style="animation-delay: calc(0.2s + {{ $loop->index }} * 0.03s)">
            <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" class="division-card-logo">
            <div class="division-card-info">
                <span class="division-card-name {{ $division->isShutdown() ? 'shutdown' : '' }}">{{ $division->name }}</span>
                <span class="division-card-count">{{ $division->members_count }} members</span>
            </div>
            @unless($division->isShutdown())
                <div class="division-card-stats">
                    @if(isset($stats['voice']))
                        <span class="division-card-stat" title="Voice active (7 days)">
                            <i class="fa fa-headset"></i> {{ $stats['voice'] }}
                        </span>
                    @endif
                    @if(($stats['recruits'] ?? 0) > 0)
                        <span class="division-card-stat" title="Recruits this month">
                            <i class="fa fa-user-plus"></i> {{ $stats['recruits'] }}
                        </span>
                    @endif
                </div>
            @endunless
        </a>
    @endforeach
</div>