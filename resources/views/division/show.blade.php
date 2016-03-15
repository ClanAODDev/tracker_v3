@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('divisions', $division) !!}

    <div class="row">

        <div class="col-xs-6">
            <h2>
                <img src="/images/game_icons/48x48/{{ $division->abbreviation }}.png"/>
                <strong>{{ $division->name }} Division</strong>
            </h2>
        </div>

        <div class="col-xs-6">
            {{-- if user is division leader --}}
            @if (Auth::user()->role->id >= 3 || Auth::user()->developer)
                <div class="btn-group pull-right">
                    <a class="btn btn-default edit-div disabled" href="#" target="_blank"><i class="fa fa-pencil"></i>
                        <span class="hidden-xs hidden-sm">Edit Division</span></a>
                </div>
            @endif
        </div>

    </div>

    <hr/>

    <div class="row">
        <div class="col-md-8">
            @include('division.partials.all_platoons')
        </div>
        <div class="col-md-4">
            @include('division.partials.division_leadership')
        </div>
    </div>

    <h3>Division Statistics</h3>
    <hr />

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">Total Members</div>

                <div class="panel-body count-detail-big striped-bg">
                    <span class="count-animated">{{ $division->members->count() }}</span>
                </div>
            </div>
        </div>
    </div>

@endsection
