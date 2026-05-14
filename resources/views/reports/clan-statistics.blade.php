@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Statistics
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-graph2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Clan statistics and demographic information
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>Tracker data is synced with the Clan AOD forums once every hour. Census statistics are polled once a week. Division specific populations include annotations which may be viewed on individual division pages.</p>
            </div>
        </div>

        <div class="report-stats" style="margin-bottom: 20px;">
            @if($previousCensus)
                @php
                    $percentChange = $memberCount > 0
                        ? abs(round((1 - $previousCensus->count / $memberCount) * 100, 2))
                        : 0;
                    $isGrowth = $memberCount >= $previousCensus->count;
                @endphp
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($memberCount) }}</div>
                    <div class="report-stat-label">Current Members</div>
                    <div class="report-stat-change {{ $isGrowth ? 'report-stat-change--up' : 'report-stat-change--down' }}">
                        <i class="fa fa-arrow-{{ $isGrowth ? 'up' : 'down' }}"></i>
                        {{ $percentChange }}% from previous census
                    </div>
                </div>
            @endif
            @if($milestones->first)
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($milestones->first->total) }}</div>
                    <div class="report-stat-label">First Census</div>
                    <div class="report-stat-change">
                        {{ \Carbon\Carbon::parse($milestones->first->date)->format('M j, Y') }}
                    </div>
                </div>
            @endif
            @if($milestones->peak)
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($milestones->peak->total) }}</div>
                    <div class="report-stat-label">Peak Census</div>
                    <div class="report-stat-change">
                        {{ \Carbon\Carbon::parse($milestones->peak->date)->format('M j, Y') }}
                    </div>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-md-8">

                @php
                    $presets = [
                        '4W'  => now()->subWeeks(4)->format('Y-m-d'),
                        '3M'  => now()->subMonths(3)->format('Y-m-d'),
                        '6M'  => now()->subMonths(6)->format('Y-m-d'),
                        '1Y'  => now()->subWeeks(52)->format('Y-m-d'),
                        'All' => $milestones->first?->date ?? now()->subYears(20)->format('Y-m-d'),
                    ];
                    $today = now()->format('Y-m-d');
                    $activePreset = null;
                    if (!$hasDateFilter) {
                        $activePreset = '1Y';
                    } elseif ($dateRange['end'] === $today) {
                        foreach ($presets as $label => $start) {
                            if ($dateRange['start'] === $start) { $activePreset = $label; break; }
                        }
                    }
                @endphp
                <form method="GET" action="{{ route('reports.clan-census') }}" class="census-date-filter" id="census-filter-form">
                    <div class="census-filter-presets">
                        @foreach($presets as $label => $start)
                            <button type="button"
                                    class="census-preset-btn {{ $activePreset === $label ? 'active' : '' }}"
                                    data-start="{{ $start }}"
                                    data-end="{{ $today }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <div class="census-filter-custom">
                        <input type="date" class="census-filter-input" id="start" name="start" value="{{ $dateRange['start'] }}">
                        <span class="census-filter-sep">to</span>
                        <input type="date" class="census-filter-input" id="end" name="end" value="{{ $dateRange['end'] }}">
                        <button type="submit" class="census-filter-apply">Apply</button>
                        @if($hasDateFilter)
                            <a href="{{ route('reports.clan-census') }}" class="census-filter-reset">Reset</a>
                        @endif
                    </div>
                </form>

                @if($populations->isNotEmpty())
                    <div class="census-chart-container" style="margin-bottom: 20px;">
                        <div class="census-chart-header">
                            <h4 class="census-chart-title">
                                <i class="fa fa-chart-line"></i> Census History
                            </h4>
                        </div>
                        <div class="census-chart-body">
                            <div class="chart-wrapper" style="height: 300px;">
                                <canvas id="census-chart"
                                     data-populations="{{ $populations }}"
                                     data-weekly-discord="{{ $weeklyVoiceActive }}"
                                ></canvas>
                            </div>
                        </div>
                    </div>
                @endif

                @include('reports.partials.clan-census-table')
                @include('reports.partials.division-populations')
            </div>
            <div class="col-md-4">
                @include('reports.partials.rank-demographic')
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/census-graph.js'])
@endsection
