@php
    $division = $division ?? $members->first()?->division;
    $maxDays = $division?->settings()->get('inactivity_days') ?? 90;
    $now = now();
    $inactiveThreshold = $now->copy()->subDays($maxDays);

    $totalCount = $members->count();
    $onLeaveCount = $members->filter(fn ($m) => $m->leave)->count();
    $activeMembers = $members->reject(fn ($m) => $m->leave);
    $inactiveCount = $activeMembers->filter(fn ($m) => $m->last_voice_activity && $m->last_voice_activity < $inactiveThreshold)->count();

    $avgTenureDays = $members->avg(fn ($m) => $m->join_date ? $m->join_date->diffInDays($now) : 0);
    $avgTenureYears = round($avgTenureDays / 365, 1);

    $officerCount = $members->filter(fn ($m) => $m->rank->isOfficer())->count();
    $memberCount = $totalCount - $officerCount;
@endphp

<div class="panel panel-filled">
    <div class="panel-body">
        <h1 class="text-center" style="margin: unset;">
            <i class="pe pe-7s-users pe-lg text-warning"></i> {{ $totalCount }}
            <small class="slight">Members</small>
        </h1>
    </div>
</div>

@if($onLeaveCount > 0 || $inactiveCount > 0)
    <div class="panel panel-filled">
        <div class="panel-body" style="padding: 10px 15px;">
            <div class="row text-center">
                @if($onLeaveCount > 0)
                    <div class="{{ $inactiveCount > 0 ? 'col-xs-6' : 'col-xs-12' }}">
                        <div class="text-muted" style="font-size: 11px; text-transform: uppercase;"
                             title="Members with an active leave of absence">On Leave
                        </div>
                        <div style="font-size: 18px;">{{ $onLeaveCount }}</div>
                    </div>
                @endif
                @if($inactiveCount > 0)
                    <div class="{{ $onLeaveCount > 0 ? 'col-xs-6' : 'col-xs-12' }}">
                        <div class="text-muted" style="font-size: 11px; text-transform: uppercase;"
                             title="Members (not on leave) with no Discord activity in {{ $maxDays }}+ days">Inactive
                        </div>
                        <div style="font-size: 18px;" class="text-danger">{{ $inactiveCount }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="panel panel-filled hidden-xs hidden-sm">
    <div class="panel-body" style="padding: 10px 15px;">
        <div class="row text-center">
            <div class="col-xs-6">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase;"
                     title="Average time members have been in AOD">Avg Tenure
                </div>
                <div style="font-size: 18px;">{{ $avgTenureYears }}<small class="text-muted">y</small></div>
            </div>
            <div class="col-xs-6">
                <div class="text-muted" style="font-size: 11px; text-transform: uppercase;"
                     title="Officers (LCpl+) to members">Officer Ratio
                </div>
                <div style="font-size: 18px;">{{ $officerCount }}<small class="text-muted">/{{ $memberCount }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-filled hidden-xs hidden-sm">
    <div class="panel-heading" title="Days since last Discord voice activity (includes members on leave)">
        Discord Activity
    </div>
    <div class="panel-body">
        <canvas class="voice-activity-chart"
                data-labels="{{ json_encode($voiceActivityGraph['labels']) }}"
                data-values="{{ json_encode($voiceActivityGraph['values']) }}"
                data-colors="{{ json_encode($voiceActivityGraph['colors']) }}"></canvas>
    </div>
</div>
