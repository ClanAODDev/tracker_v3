@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Organization Chart
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('division-org-chart', $division) !!}

        <div class="org-chart-toolbar">
            <div class="org-chart-search-wrapper">
                <i class="fa fa-search org-chart-search-icon"></i>
                <input type="text" id="org-chart-search" class="form-control form-control-sm" placeholder="Search members...">
                <button type="button" class="btn btn-link btn-sm org-chart-clear-search" id="clear-search">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="org-chart-controls">
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id="zoom-in" title="Zoom In">
                        <i class="fa fa-search-plus"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="zoom-out" title="Zoom Out">
                        <i class="fa fa-search-minus"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="zoom-reset" title="Reset View">
                        <i class="fa fa-compress"></i>
                        <span class="btn-text">Reset</span>
                    </button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id="expand-all" title="Expand All">
                        <i class="fa fa-expand"></i>
                        <span class="btn-text">Expand</span>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="collapse-all" title="Collapse All">
                        <i class="fa fa-compress-alt"></i>
                        <span class="btn-text">Collapse</span>
                    </button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id="toggle-handles" title="Toggle Handles">
                        <i class="fa fa-gamepad"></i>
                        <span class="btn-text">Handles</span>
                    </button>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id="export-png" title="Export to PNG">
                        <i class="fa fa-download"></i>
                        <span class="btn-text">Export</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="org-chart-container">
            <div class="org-chart-loading">
                <span class="themed-spinner"></span>
                <span>Loading organization chart...</span>
            </div>
            <svg id="org-chart"></svg>
        </div>
    </div>

@endsection

@section('footer_scripts')
    @vite('resources/assets/js/org-chart.js')
    <script>
        window.orgChartConfig = {
            dataUrl: "{{ route('division.org-chart.data', $division->slug) }}",
            memberBaseUrl: "{{ url('/members') }}"
        };
    </script>
@endsection
