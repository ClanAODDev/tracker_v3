@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->abbreviation) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            </a>
        @endslot
        @slot ('heading')
            {{ $squad->name }}
            @include('squad.partials.edit-squad-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('squad', $division, $platoon, $squad) !!}

        {{--@include('platoon.partials.alerts')--}}


        <div class="row">
            <div class="col-md-9">
                <div class="panel panel-filled">
                    @include('squad.partials.squad-members')
                </div>
            </div>
            <div class="col-md-3">
                @include('platoon.partials.squads')
                @include('squad.partials.member-stats')
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2.0') !!}"></script>
@stop
