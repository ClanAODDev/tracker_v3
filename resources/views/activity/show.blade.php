@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('user', $division, $user ) !!}

    <div class="row">
        <div class="col-xs-6">

            <h2>
                <strong>{!! $user->member->present()->rankName !!}</strong>
                <small>Activity</small>
            </h2>

        </div>
    </div>

    <hr/>

    <ul class="list-group">
        @include ('activity.list')
    </ul>


@stop