@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Census Data
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('division-census', $division) !!}

        @include('division.partials.select-panel')

        <div class="report-stats">
            <div class="report-stat">
                <div class="report-stat-value">{{ $stats['population'] }}</div>
                <div class="report-stat-label">Population</div>
                @if($stats['popChange'] !== 0)
                    <div class="report-stat-change {{ $stats['popChange'] > 0 ? 'report-stat-change--up' : 'report-stat-change--down' }}">
                        <i class="fa fa-{{ $stats['popChange'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($stats['popChange']) }} from last week
                    </div>
                @endif
            </div>
            <div class="report-stat">
                <div class="report-stat-value">{{ $stats['voicePercent'] }}<span class="report-stat-unit">%</span></div>
                <div class="report-stat-label">Voice Active</div>
                @if($stats['voiceChange'] !== 0.0)
                    <div class="report-stat-change {{ $stats['voiceChange'] > 0 ? 'report-stat-change--up' : 'report-stat-change--down' }}">
                        <i class="fa fa-{{ $stats['voiceChange'] > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($stats['voiceChange']) }}% from last week
                    </div>
                @endif
            </div>
            <div class="report-stat">
                <div class="report-stat-value">{{ $stats['avgVoice'] }}<span class="report-stat-unit">%</span></div>
                <div class="report-stat-label">4-Week Avg Voice</div>
            </div>
        </div>

        @include('division.partials.census-graph')

        @include('division.forms.census')

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/census-graph.js'])
@endsection
