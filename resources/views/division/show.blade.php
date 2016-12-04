@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('division', $division) !!}

    <div class="row">
        <div class="col-xs-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $division->name }}</strong>

                @can('update', $division)
                    <a title="Edit division" class="btn btn-default"
                       href="{{ action('DivisionController@edit', $division->abbreviation) }}">
                        <i class="fa fa-cogs fa-lg"></i>
                    </a>
                @endcan

            </h2>
        </div>

        <div class="col-xs-6">
            <ul class="nav nav-pills pull-right">

                <li class="active">
                    <a href="#"><i class="fa fa-gamepad fa-lg"></i><span class="hidden-xs hidden-sm">Overview</span></a>
                </li>

                <li>
                    <a href="{{ action('DivisionController@statistics', $division->abbreviation) }}"><i class="fa fa-bar-chart fa-lg"></i><span class="hidden-xs hidden-sm">Statistics</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-md-6">
            @include('division.partials.platoons')
        </div>
        <div class="col-md-6">
            @include('division.partials.leadership')
        </div>
    </div>

@stop