@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

    <div class="row">
        <div class="col-xs-12">
            <h2>
                @include('division.partials.icon')

                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>

    <ul class="nav nav-pills margin-top-20">
        <li class="active">
            <a href="#">
                <i class="fa fa-cube fa-lg"></i>
                {{ $division->locality('platoon') }}
            </a>
        </li>

        @if($platoon->squads->count())
            <li>
                <a href="{{ action('SquadController@index', [$division->abbreviation, $platoon->id]) }}">
                    <i class="fa fa-cubes fa-lg"></i>
                    {{ str_plural($division->locality('squad')) }}
                </a>
            </li>
        @endif
    </ul>

    <div class="row margin-top-20">
        <div class="col-md-8">
            @include('platoon.partials.platoon_members')
        </div>

        <div class="col-md-4">
            @include('platoon.partials.member_stats')
        </div>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
