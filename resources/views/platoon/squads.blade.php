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
        <li>
            <a href="{{ action('PlatoonController@show', $platoon->id) }}">
                <i class="fa fa-cube fa-lg"></i>
                {{ ucwords($division->locality('platoon')) }}
            </a>
        </li>

        <li class="active">
            <a href="#">
                <i class="fa fa-cubes fa-lg"></i>
                {{ str_plural(ucwords($division->locality('squad'))) }}
            </a>
        </li>
    </ul>


    @include('platoon.partials.squads')

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
