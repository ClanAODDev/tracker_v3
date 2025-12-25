@php
    $currentCount = $stats->memberCount;
    $previousCount = $previousCensus?->count ?? 0;
    $percentChange = ($previousCount && $currentCount)
        ? abs(round((1 - $previousCount / $currentCount) * 100, 1))
        : 0;
    $isDecline = $currentCount < $previousCount;

    $populationTrend = array_slice($chartData['population'], -8);
    $voiceTrend = array_slice($chartData['voiceActive'], -8);
    $voiceRateTrend = [];
    for ($i = 0; $i < count($voiceTrend); $i++) {
        $pop = $populationTrend[$i] ?? 1;
        $voiceRateTrend[] = $pop > 0 ? round(($voiceTrend[$i] / $pop) * 100) : 0;
    }
@endphp
<div class="row quick-stats">
    <div class="col-md-4 col-sm-6 animate-fade-in-up" style="animation-delay: 0.05s">
        <div class="panel panel-filled">
            <div class="panel-body">
                <div class="stat-icon">
                    <i class="fa fa-users text-info"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">
                        {{ number_format($stats->memberCount) }}
                        @if($percentChange > 0)
                            <small class="{{ $isDecline ? 'text-danger' : 'text-success' }}">
                                <i class="fa fa-caret-{{ $isDecline ? 'down' : 'up' }}"></i>
                                {{ $percentChange }}%
                            </small>
                        @endif
                    </span>
                    <span class="stat-label">Total Members</span>
                </div>
                @if(count($populationTrend) > 1)
                    <div class="stat-sparkline">
                        <x-sparkline :data="$populationTrend" :width="80" :height="24" :trend="$isDecline ? 'down' : 'up'" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6 animate-fade-in-up" style="animation-delay: 0.1s">
        <div class="panel panel-filled">
            <div class="panel-body">
                <div class="stat-icon">
                    <i class="fa fa-headset {{ $stats->voiceRate >= 30 ? 'text-success' : ($stats->voiceRate >= 15 ? 'text-warning' : 'text-danger') }}"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">
                        {{ number_format($stats->voiceActiveCount) }}
                        <small class="{{ $stats->voiceRate >= 30 ? 'text-success' : ($stats->voiceRate >= 15 ? 'text-warning' : 'text-danger') }}">
                            {{ $stats->voiceRate }}%
                        </small>
                    </span>
                    <span class="stat-label">Voice Active</span>
                </div>
                @if(count($voiceRateTrend) > 1)
                    <div class="stat-sparkline">
                        <x-sparkline :data="$voiceRateTrend" :width="80" :height="24" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6 animate-fade-in-up" style="animation-delay: 0.15s">
        <div class="panel panel-filled">
            <div class="panel-body">
                <div class="stat-icon">
                    <i class="fa fa-user-plus text-success"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats->recruitsLast30Days }}</span>
                    <span class="stat-label">Recruits <small class="text-muted">(30 days)</small></span>
                </div>
            </div>
        </div>
    </div>
</div>
