@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Training
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Training Module
        @endslot
    @endcomponent

    <div class="container-fluid" id="training-container">
        <training-module></training-module>
    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/training.js?v=0.1.0') !!}"></script>
@stop