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

    <div class="panel panel-default">

        <div class="panel-heading">
            Test
        </div>

        <div class="panel-body">

            <ul class="list-group">
                <a href="#" class="list-group-item col-xs-6">Row1</a>
                <a href="#" class="list-group-item col-xs-6">Row2</a>
                <a href="#" class="list-group-item col-xs-6">Row3</a>
                <a href="#" class="list-group-item col-xs-6">Row4</a>
                <a href="#" class="list-group-item col-xs-6">Row5</a>
            </ul>
        </div>
    </div>

@stop