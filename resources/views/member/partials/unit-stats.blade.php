<div class="panel panel-filled">
    <div class="panel-body">
        <h1 class="text-center unit-stats-total">
            <i class="pe pe-7s-users pe-lg text-warning"></i> {{ $unitStats->totalCount }}
            <small class="slight">Members</small>
        </h1>
    </div>
</div>

@if($unitStats->onLeaveCount > 0 || $unitStats->inactiveCount > 0)
    <div class="panel panel-filled">
        <div class="panel-body unit-stats-row">
            <div class="row text-center">
                @if($unitStats->onLeaveCount > 0)
                    <div class="{{ $unitStats->inactiveCount > 0 ? 'col-xs-6' : 'col-xs-12' }}">
                        <div class="unit-stats-label" title="Members with an active leave of absence">On Leave</div>
                        <div class="unit-stats-value">{{ $unitStats->onLeaveCount }}</div>
                    </div>
                @endif
                @if($unitStats->inactiveCount > 0)
                    <div class="{{ $unitStats->onLeaveCount > 0 ? 'col-xs-6' : 'col-xs-12' }}">
                        <div class="unit-stats-label" title="Members (not on leave) with no Discord activity in {{ $unitStats->inactivityDays }}+ days">Inactive</div>
                        <div class="unit-stats-value text-danger">{{ $unitStats->inactiveCount }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="panel panel-filled hidden-xs hidden-sm">
    <div class="panel-body unit-stats-row">
        <div class="row text-center">
            <div class="col-xs-6">
                <div class="unit-stats-label" title="Average time members have been in AOD">Avg Tenure</div>
                <div class="unit-stats-value">{{ $unitStats->avgTenureYears }}<small class="text-muted">y</small></div>
            </div>
            <div class="col-xs-6">
                <div class="unit-stats-label" title="Officers (LCpl+) to members">Officer Ratio</div>
                <div class="unit-stats-value">{{ $unitStats->officerCount }}<small class="text-muted">/{{ $unitStats->memberCount }}</small></div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-filled hidden-xs hidden-sm">
    <div class="panel-heading" title="Days since last Discord voice activity (includes members on leave)">
        Discord Activity
    </div>
    <div class="panel-body">
        <div class="chart-wrapper" style="height: 180px;">
            <canvas id="voice-activity-chart"
                 data-labels="{{ json_encode($unitStats->voiceActivityGraph['labels']) }}"
                 data-values="{{ json_encode($unitStats->voiceActivityGraph['values']) }}"
                 data-colors="{{ json_encode($unitStats->voiceActivityGraph['colors']) }}"></canvas>
        </div>
    </div>
</div>
