@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

    <div class="row">
        <div class="col-sm-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
        <div class="col-sm-6">
            <ul class="nav nav-pills pull-right">
                <li>
                    <a href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}">
                        <i class="fa fa-cube fa-lg"></i>
                        {{ ucwords($division->locality('platoon')) }} View
                    </a>
                </li>

                <li class="active">
                    <a href="#">
                        <i class="fa fa-cubes fa-lg"></i>
                        {{ ucwords($division->locality('squad')) }} View
                    </a>
                </li>
            </ul>
        </div>
    </div>


    <hr />

    <div class="row margin-top-20">

        <div class="col-md-4">
            @include('platoon.partials.unassigned')
        </div>

        <div class="col-md-8">
            @include('platoon.partials.squads')
        </div>

    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
