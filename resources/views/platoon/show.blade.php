@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

    <div class="row">
        <div class="col-xs-12">
            <h2>
                <img src="/images/game_icons/48x48/{{ $division->abbreviation }}.png"/>
                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-md-8">
            @include('platoon.partials.platoon_members')
        </div>
        <div class="col-md-4">
            @include('platoon.partials.member_stats')
        </div>
    </div>


    @if (count($platoon->squads->count()))
        <div class="row">
            @include('platoon.partials.squads')
        </div>
    @endif


@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
    <script src="{!! asset('/js/activity.js') !!}"></script>
@stop
