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

    <form id="create-squad" method="post" class="well margin-top-20"
          action="{{ route('saveSquad', [$division->abbreviation, $platoon->id]) }}">

        @include('squad.form', ['actionText' => 'Create New'])

    </form>

@stop
