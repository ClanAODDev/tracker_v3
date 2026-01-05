@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Member Retention
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('retention-report', $division) !!}

        @include('division.partials.select-panel')

        <div class="report-stats">
            <div class="report-stat report-stat--success">
                <div class="report-stat-value">{{ $stats['recruits'] }}</div>
                <div class="report-stat-label">Recruited</div>
            </div>
            <div class="report-stat report-stat--warning">
                <div class="report-stat-value">{{ $stats['removals'] }}</div>
                <div class="report-stat-label">Removed</div>
            </div>
            <div class="report-stat {{ $stats['netChange'] >= 0 ? 'report-stat--info' : 'report-stat--danger' }}">
                <div class="report-stat-value">
                    @if($stats['netChange'] > 0)+@endif{{ $stats['netChange'] }}
                </div>
                <div class="report-stat-label">Net Change</div>
            </div>
            <div class="report-stat">
                <div class="report-stat-value">{{ $stats['retentionRate'] }}<span class="report-stat-unit">%</span></div>
                <div class="report-stat-label">Retention Rate</div>
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

        <div class="retention-chart-container">
            <div class="retention-chart-header">
                <h4 class="retention-chart-title">
                    <i class="fa fa-chart-area"></i> Retention Trends
                </h4>
            </div>
            <div class="retention-chart-body">
                <div class="chart-wrapper" style="height: 300px;">
                    <canvas id="retention-chart"
                         data-recruits="{{ $recruits }}"
                         data-removals="{{ $removals }}"
                         data-population="{{ $population }}"
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="retention-leaderboard-container">
            <div class="retention-leaderboard-header">
                <h4 class="retention-leaderboard-title">
                    <i class="fa fa-trophy"></i> Top Recruiters
                </h4>
                <span class="retention-leaderboard-total">{{ $totalRecruitCount }} total</span>
            </div>
            <div class="retention-leaderboard-body">
                @forelse ($members as $index => $item)
                    @if (isset($item['member']) && is_object($item['member']))
                        <a href="{{ route('member', $item['member']->getUrlParams()) }}" class="retention-leaderboard-item">
                            <span class="retention-leaderboard-rank {{ $index < 3 ? 'retention-leaderboard-rank--top' : '' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="retention-leaderboard-name">
                                {{ $item['member']->present()->rankName }}
                            </span>
                            <span class="retention-leaderboard-count">
                                {{ $item['recruits'] }}
                            </span>
                        </a>
                    @endif
                @empty
                    <div class="retention-leaderboard-empty">
                        No recruits in this period
                    </div>
                @endforelse
            </div>
        </div>

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/retention-graph.js'])
@endsection
