@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('create-platoon', $division) !!}



    <div class="row">
        <div class="col-xs-12">
            <h2>
                @include('division.partials.icon')

                <strong>{{ $division->name }}</strong>
                <small>Create Platoon</small>
            </h2>
        </div>
    </div>


@stop


