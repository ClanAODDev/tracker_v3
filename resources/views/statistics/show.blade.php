@extends('application.base')

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

        <div class="row">
            <div class="col-md-8">
                @include('statistics.partials.member-census-count')
                @include('statistics.partials.division-populations')
            </div>
            <div class="col-md-4">
                @include('statistics.partials.rankDemographic')
            </div>
        </div>
    </div>
@stop

