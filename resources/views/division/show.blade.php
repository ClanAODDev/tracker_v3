@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('division', $division) !!}

    <div class="row">
        <div class="col-xs-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $division->name }}</strong>
            </h2>
        </div>

        <div class="col-xs-6">
            <div class="pull-right">
                <a class="btn btn-info"
                   href="{{ action('DivisionController@statistics', $division->abbreviation) }}"><i class="fa fa-bar-chart"></i><span class="hidden-xs hidden-sm">Statistics</span></a>

                @can('update', $division)
                    <a class="btn btn-default" href="{{ action('DivisionController@edit', $division->abbreviation) }}"><i class="fa fa-pencil"></i>
                        <span class="hidden-xs hidden-sm">Edit Division</span></a>
                @endcan

            </div>

        </div>

    </div>

    <hr/>

    <div class="row">
        <div class="col-md-8">
            @include('division.partials.platoons')
        </div>
        <div class="col-md-4">
            @include('division.partials.division_leadership')
        </div>
    </div>
@stop