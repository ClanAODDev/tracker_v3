@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('division', $division) !!}

    <h2>
        <img src="/images/game_icons/48x48/{{ $division->abbreviation }}.png"/>
        <strong>{{ $division->name }} Division</strong>
    </h2>
    <hr/>

    @forelse ($division->squads as $squad)
        <li class="list-group-item">
            Squad #{{ $squad->id }}
        </li>
    @empty
        <p>No squads exist for this division</p>
    @endforelse
    
@stop
