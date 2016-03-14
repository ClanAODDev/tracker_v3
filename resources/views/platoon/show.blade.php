@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoons', $platoon->division, $platoon) !!}

    <h1>
        <img src="/images/game_icons/48x48/{{ $platoon->division->abbreviation }}.png"/>
        <strong>{{ $platoon->name }}</strong>
    </h1>

    <hr/>

    <div class="row">

        <div class="col-md-8">
            @include('platoon.partials.platoon_members')
        </div>

        <div class="col-md-4">
            @include('platoon.partials.member_stats')
        </div>

    </div>

@stop