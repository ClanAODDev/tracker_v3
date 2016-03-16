@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoon', $platoon->division, $platoon) !!}

    <h2>
        <img src="/images/game_icons/48x48/{{ $platoon->division->abbreviation }}.png"/>
        <strong>{{ $platoon->name }}</strong> <small>{{ $platoon->division->name }}</small>
    </h2>

    <hr />

    @foreach ($platoon->squads as $squad)
        <li class="list-group-item">
            Squad #{{ $squad->id }}
        </li>
    @endforeach

@stop
