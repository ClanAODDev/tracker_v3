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

        <div class="row">
            <div class="col-md-12">
                @include('statistics.partials.member-census-count')
            </div>
        </div>

        <div class="panel panel-filled table-responsive">
            <div class="panel-body">
                Division census information within the past week.
            </div>
            @include('statistics.partials.division-populations')
        </div>

    </div>
@stop

