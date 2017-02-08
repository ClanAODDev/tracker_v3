@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('squad', $division, $platoon) !!}

    <div class="row">
        <div class="col-sm-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>

    {!! Form::model($squad, ['class' => 'well', 'method' => 'patch', 'route' => ['updateSquad', $division->abbreviation, $platoon, $squad]]) !!}

        @include('squad.form', ['actionText' => 'Update'])

    {!! Form::close() !!}

@stop
