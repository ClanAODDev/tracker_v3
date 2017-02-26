@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('squads', $division) !!}

    <h2>
        @include('division.partials.icon')
        <strong>{{ $division->name }}</strong>
        <small>Squads</small>
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
