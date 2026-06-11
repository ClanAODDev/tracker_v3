@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Transfer Origins
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('transfer-report', $division) !!}

        @include('division.partials.select-panel')

        <div class="report-stats">
            <div class="report-stat report-stat--primary">
                <div class="report-stat-value">{{ $stats['total'] }}</div>
                <div class="report-stat-label">Total Members</div>
            </div>
            <div class="report-stat report-stat--info">
                <div class="report-stat-value">{{ $sources->where('is_original', false)->sum('count') }}</div>
                <div class="report-stat-label">Transferred In</div>
            </div>
            <div class="report-stat report-stat--success">
                <div class="report-stat-value">{{ $sources->where('is_original', true)->sum('count') }}</div>
                <div class="report-stat-label">Started Here</div>
            </div>
            <div class="report-stat">
                <div class="report-stat-value">{{ $stats['sources'] }}</div>
                <div class="report-stat-label">Source Divisions</div>
            </div>
        </div>

        <div class="report-filter">
            <form class="report-filter-form">
                <div class="report-filter-group">
                    <label for="start">Start</label>
                    <input type="date" class="form-control" name="start" id="start" value="{{ $range['start'] }}">
                </div>
                <div class="report-filter-group">
                    <label for="end">End</label>
                    <input type="date" class="form-control" name="end" id="end" value="{{ $range['end'] }}">
                </div>
                <button type="submit" class="btn btn-accent">
                    <i class="fa fa-filter"></i> Apply
                </button>
                <a href="{{ route('division.transfer-report', $division) }}" class="btn btn-default">
                    <i class="fa fa-times"></i> Clear
                </a>
                <div class="report-filter-presets">
                    <select id="preset-select" class="form-control">
                        <option value="">Quick Select...</option>
                        <option value="3">Last 3 Months</option>
                        <option value="6">Last 6 Months</option>
                        <option value="12">Last Year</option>
                        <option value="24">Last 2 Years</option>
                    </select>
                </div>
            </form>
        </div>

        @if($sources->isNotEmpty())
            <div class="retention-chart-container">
                <div class="retention-chart-header">
                    <h4 class="retention-chart-title">
                        <i class="fa fa-exchange-alt"></i> Transfers by Source Division
                    </h4>
                </div>
                <div class="retention-chart-body">
                    <div class="chart-wrapper" style="height: 300px;">
                        <canvas id="transfer-chart"
                            data-labels="{{ json_encode($sources->pluck('name')) }}"
                            data-counts="{{ json_encode($sources->pluck('count')) }}"
                            data-original-index="{{ $sources->search(fn($s) => $s['is_original']) }}"
                        ></canvas>
                    </div>
                </div>
            </div>

            <div class="retention-leaderboard-container">
                <div class="retention-leaderboard-header">
                    <h4 class="retention-leaderboard-title">
                        <i class="fa fa-list"></i> Breakdown
                    </h4>
                    <span class="retention-leaderboard-total">{{ $stats['total'] }} total</span>
                </div>
                <div class="retention-leaderboard-body">
                    @foreach ($sources as $index => $source)
                        <div class="retention-leaderboard-item {{ $source['is_original'] ? 'retention-leaderboard-item--muted' : '' }}">
                            @if($source['is_original'])
                                <span class="retention-leaderboard-rank">
                                    <i class="fa fa-home"></i>
                                </span>
                            @else
                                <span class="retention-leaderboard-rank {{ $index < 3 ? 'retention-leaderboard-rank--top' : '' }}">
                                    {{ $index + 1 }}
                                </span>
                            @endif
                            <span class="retention-leaderboard-name">{{ $source['name'] }}</span>
                            <span class="retention-leaderboard-count">
                                {{ $source['count'] }}
                                <span class="text-muted" style="font-size: 0.85em;">({{ $source['percentage'] }}%)</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="report-empty">
                <i class="fa fa-exchange-alt"></i>
                <h4>No Transfers Found</h4>
                <p>No approved transfers into {{ $division->name }}{{ $range['start'] || $range['end'] ? ' in this date range' : '' }}.</p>
            </div>
        @endif

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/transfer-graph.js'])
@endsection
