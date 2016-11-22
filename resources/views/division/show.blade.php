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
            {{-- if user is division leader --}}

                <div class="btn-group pull-right">
                    @can('update', $division)
                    <a class="btn btn-default" href="{{ action('DivisionController@edit', $division->abbreviation) }}"><i class="fa fa-pencil"></i>
                        <span class="hidden-xs hidden-sm">Edit Division</span></a>
                    @endcan
                    <a class="btn btn-default" href="{{ action('DivisionController@statistics', $division->abbreviation) }}"><i class="fa fa-bar-chart"></i>
                        <span class="hidden-xs hidden-sm">Statistics</span></a>
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

    <h3>Demographics</h3>
    <hr/>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">Active Members</div>

                <div class="panel-body count-detail-big striped-bg">
                    <span class="count-animated">{{ $division->activeMembers->count() }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">Part-time Members</div>

                <div class="panel-body count-detail-big striped-bg">
                    <span class="count-animated">{{ $division->partTimeMembers->count() }}</span>
                </div>
            </div>
        </div>
    </div>
@stop