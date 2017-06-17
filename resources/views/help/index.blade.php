@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Documentation
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Documentation and frequently asked questions
        @endslot
    @endcomponent

    <div class="container-fluid">

        @include('help.partials.search')
        @include('help.partials.navigation')
        @include('help.partials.general')
        @include('help.partials.division-structures')

        <div class="panel m-t-xl">
            @include('help.partials.access-roles')
        </div>

    </div>
@stop
