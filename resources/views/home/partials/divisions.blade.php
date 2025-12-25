<div class="divisions-grid">
    @foreach($divisions as $division)
        <a href="{{ route('division', $division->slug) }}" class="division-card animate-scale-in animate-stagger {{ $division->isShutdown() ? 'division-card-shutdown' : '' }}" style="animation-delay: calc(0.2s + {{ $loop->index }} * 0.03s)">
            <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" class="division-card-logo">
            <div class="division-card-info">
                <span class="division-card-name {{ $division->isShutdown() ? 'shutdown' : '' }}">{{ $division->name }}</span>
                <span class="division-card-count">{{ $division->members_count }} members</span>
            </div>
        </a>
    @endforeach
</div>