@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoon', $division) !!}



    <div class="row">
        <div class="col-xs-12">
            <h2>
                @include('division.partials.icon')

                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>


@stop


