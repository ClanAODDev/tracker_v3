@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('statistics', $division) !!}

    <div class="row">
        <div class="col-xs-6">
            <h2>
                @include('division.partials.icon')
                <strong class="hidden-xs">{{ $division->name }}</strong>
                <small class="hidden-xs">Statistics</small>
            </h2>
        </div>
        <div class="col-xs-6">
            <ul class="nav nav-pills pull-right">

                <li>
                    <a href="{{ route('division', $division->abbreviation) }}"><i class="fa fa-gamepad fa-lg"></i><span
                                class="hidden-xs hidden-sm">Overview</span></a>
                </li>

                <li class="active">
                    <a href="#"><i class="fa fa-bar-chart fa-lg"></i><span class="hidden-xs hidden-sm">Statistics</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <hr />

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
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">Division Activity</div>
                <div class="panel-body">
                    {!! $activity->render() !!}
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="panel panel-default">
                <div class="panel-heading">Membership By Rank</div>
                <div class="panel-body">
                    {!! $rankDemographic->render() !!}
                </div>
            </div>
        </div>
    </div>

@stop
