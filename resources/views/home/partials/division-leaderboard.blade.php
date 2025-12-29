<div class="division-leaderboard animate-fade-in-up" style="animation-delay: 0.15s">
    {{-- Mobile: Tabbed interface --}}
    <div class="leaderboard-mobile">
        <div class="leaderboard-header">
            <div class="leaderboard-title">
                <i class="fa fa-trophy"></i>
                <span>Leaderboard</span>
            </div>
        </div>

        <div class="leaderboard-tabs">
            <button class="leaderboard-tab active" data-tab="recruits">
                <i class="fa fa-user-plus"></i>
                Recruits
            </button>
            <button class="leaderboard-tab" data-tab="voice">
                <i class="fa fa-headset"></i>
                Voice %
            </button>
            <button class="leaderboard-tab" data-tab="growth">
                <i class="fa fa-chart-line"></i>
                Growth
            </button>
        </div>

        <div class="leaderboard-content">
            <div class="leaderboard-panel active" data-panel="recruits">
                @foreach($leaderboard->recruitLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        <span class="leaderboard-value">{{ $division['formatted'] }}</span>
                    </a>
                @endforeach
                <div class="leaderboard-footer text-muted">New members since {{ now()->startOfMonth()->format('M j') }}</div>
            </div>

            <div class="leaderboard-panel" data-panel="voice">
                @foreach($leaderboard->voiceLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        @if(count($division['trend']) > 1)
                            <x-sparkline :data="$division['trend']" :trend="$division['trending']" />
                        @endif
                        <span class="leaderboard-value">{{ $division['formatted'] }}</span>
                    </a>
                @endforeach
                <div class="leaderboard-footer text-muted">Weekly voice active / total members</div>
            </div>

            <div class="leaderboard-panel" data-panel="growth">
                @foreach($leaderboard->growthLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        @if(count($division['trend']) > 1)
                            <x-sparkline :data="$division['trend']" :trend="$division['value'] > 0 ? 'up' : ($division['value'] < 0 ? 'down' : 'neutral')" />
                        @endif
                        <span class="leaderboard-value {{ $division['value'] > 0 ? 'leaderboard-value--positive' : ($division['value'] < 0 ? 'leaderboard-value--negative' : '') }}">
                            {{ $division['formatted'] }}
                        </span>
                    </a>
                @endforeach
                <div class="leaderboard-footer text-muted">Member change since last census</div>
            </div>
        </div>
    </div>

    {{-- Desktop: Grid of all three --}}
    <div class="leaderboard-grid">
        <div class="leaderboard-card animate-fade-in-up" style="animation-delay: 0.1s">
            <div class="leaderboard-card-header">
                <i class="fa fa-user-plus"></i>
                <span>Recruits</span>
                <span class="leaderboard-card-period">Monthly</span>
            </div>
            <div class="leaderboard-card-body">
                @foreach($leaderboard->recruitLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        <span class="leaderboard-value">{{ $division['formatted'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="leaderboard-card-footer">Since {{ now()->startOfMonth()->format('M j') }}</div>
        </div>

        <div class="leaderboard-card animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="leaderboard-card-header">
                <i class="fa fa-headset"></i>
                <span>Voice %</span>
                <span class="leaderboard-card-period">Weekly</span>
            </div>
            <div class="leaderboard-card-body">
                @foreach($leaderboard->voiceLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        @if(count($division['trend']) > 1)
                            <x-sparkline :data="$division['trend']" :trend="$division['trending']" />
                        @endif
                        <span class="leaderboard-value">{{ $division['formatted'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="leaderboard-card-footer">Voice active / total members</div>
        </div>

        <div class="leaderboard-card animate-fade-in-up" style="animation-delay: 0.3s">
            <div class="leaderboard-card-header">
                <i class="fa fa-chart-line"></i>
                <span>Growth</span>
                <span class="leaderboard-card-period">Weekly</span>
            </div>
            <div class="leaderboard-card-body">
                @foreach($leaderboard->growthLeaders as $index => $division)
                    <a href="{{ route('division', $division['slug']) }}"
                       class="leaderboard-item {{ $division['id'] === $leaderboard->userDivisionId ? 'leaderboard-item--highlight' : '' }}">
                        <span class="leaderboard-rank leaderboard-rank--{{ $index + 1 }}">{{ $index + 1 }}</span>
                        <img src="{{ $division['logo'] ?? getThemedLogoPath() }}" class="leaderboard-logo" alt="">
                        <span class="leaderboard-name">{{ $division['name'] }}</span>
                        @if(count($division['trend']) > 1)
                            <x-sparkline :data="$division['trend']" :trend="$division['value'] > 0 ? 'up' : ($division['value'] < 0 ? 'down' : 'neutral')" />
                        @endif
                        <span class="leaderboard-value {{ $division['value'] > 0 ? 'leaderboard-value--positive' : ($division['value'] < 0 ? 'leaderboard-value--negative' : '') }}">
                            {{ $division['formatted'] }}
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="leaderboard-card-footer">Change since last census</div>
        </div>
    </div>
</div>
