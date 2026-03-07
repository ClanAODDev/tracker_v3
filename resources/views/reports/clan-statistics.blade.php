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

        @if($milestones->first && $milestones->peak)
            <div class="report-stats" style="margin-bottom: 20px;">
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($milestones->first->total) }}</div>
                    <div class="report-stat-label">First Census</div>
                    <div class="report-stat-change">
                        {{ \Carbon\Carbon::parse($milestones->first->date)->format('M j, Y') }}
                    </div>
                </div>
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($milestones->peak->total) }}</div>
                    <div class="report-stat-label">Peak Census</div>
                    <div class="report-stat-change">
                        {{ \Carbon\Carbon::parse($milestones->peak->date)->format('M j, Y') }}
                    </div>
                </div>
                <div class="report-stat">
                    <div class="report-stat-value">{{ number_format($memberCount) }}</div>
                    <div class="report-stat-label">Current Members</div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                @include('reports.partials.member-census-count')

                <div class="census-date-filter" style="margin-bottom: 20px;">
                    <form method="GET" action="{{ route('reports.clan-census') }}" class="form-inline">
                        <div class="form-group" style="margin-right: 10px;">
                            <label for="start" style="margin-right: 5px;">From</label>
                            <input type="date" class="form-control" id="start" name="start" value="{{ $dateRange['start'] }}">
                        </div>
                        <div class="form-group" style="margin-right: 10px;">
                            <label for="end" style="margin-right: 5px;">To</label>
                            <input type="date" class="form-control" id="end" name="end" value="{{ $dateRange['end'] }}">
                        </div>
                        <button type="submit" class="btn btn-default">Apply</button>
                        @if($hasDateFilter)
                            <a href="{{ route('reports.clan-census') }}" class="btn btn-link" style="margin-left: 5px;">Reset</a>
                        @endif
                    </form>
                </div>

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
