@extends('application.base')
@section('content')

    <div class="division-header">
        <div class="header-icon">
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        </div>
        <div class="header-title">
            <h3 class="m-b-xs text-uppercase">
                {{ $platoon->name }} {{ $division->locality('platoon') }}
            </h3>
            <small>
                {{ $division->name }} Division
            </small>
        </div>
    </div>

    <hr />

    <div class="container-fluid">

        <div class="row margin-top-20">
            <div class="col-md-12">
                @include('platoon.partials.platoon-members')
            </div>

            <div class="col-md-4">
                {{--@include('platoon.partials.member_stats')--}}
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
