@php
    $currentCount = $stats->memberCount;
    $previousCount = $previousCensus?->count ?? 0;
    $percentChange = ($previousCount && $currentCount)
        ? abs(round((1 - $previousCount / $currentCount) * 100, 1))
        : 0;
    $isDecline = $currentCount < $previousCount;
@endphp
<div class="row quick-stats">
    <div class="col-md-4 col-sm-6">
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
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="panel panel-filled">
            <div class="panel-body">
                <div class="stat-icon">
                    <i class="fa fa-headset {{ $stats->voiceRate >= 30 ? 'text-success' : ($stats->voiceRate >= 15 ? 'text-warning' : 'text-danger') }}"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value">{{ $stats->voiceRate }}%</span>
                    <span class="stat-label">Voice Active <small class="text-muted">({{ $stats->voiceActiveCount }})</small></span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
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
