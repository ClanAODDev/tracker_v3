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

        <div class="alert alert-default">
            <i class="fa fa-exclamation-triangle text-danger"></i>
            There are
            <code>{{ count($mismatchedTSMembers) }}</code> members improperly configured for Teamspeak. Please review the
            <a href="{{ route('reports.clan-ts-report') }}"
               class="alert-link">Teamspeak Report</a> to resolve these issues.
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>Tracker data is synced with the Clan AOD forums once every hour. Census statistics are polled once a week. Division specific populations include annotations which may be viewed on individual division pages.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                @include('reports.partials.member-census-count')
                @include('reports.partials.division-populations')
            </div>
            <div class="col-md-4">
                @include('reports.partials.rank-demographic')
            </div>
        </div>
    </div>
@endsection

