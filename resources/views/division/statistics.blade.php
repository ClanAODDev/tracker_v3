@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('statistics', $division) !!}

    <div class="row">
        <div class="col-xs-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $division->name }}</strong> <small>Statistics</small>
            </h2>
        </div>
    </div>

    <hr/>

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

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">Division Activity</div>
                <div class="panel-body">
                    {!! $activity->render() !!}
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">Membership By Rank</div>
                <div class="panel-body">
                    {!! $rankDemographic->render() !!}
                </div>
            </div>
        </div>
    </div>

@stop