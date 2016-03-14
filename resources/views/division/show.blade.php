@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('divisions', $division) !!}

    <div class="page-header">
        <h2>
            <img src="/images/game_icons/48x48/{{ $division->abbreviation }}.png"/>
            <strong>{{ $division->name }} Division</strong>

            {{-- if user is division leader --}}
            @if (Auth::user()->role->id >= 3 || Auth::user()->developer)
                <div class="btn-group pull-right">
                    <a class="btn btn-default edit-div disabled" href="#" target="_blank"><i class="fa fa-pencil"></i>
                        <span class="hidden-xs hidden-sm">Edit Division</span></a>
                </div>
            @endif

        </h2>
    </div>

    <div class="row">

        <div class="col-md-8">
            @include('division.partials.all_platoons')
        </div>

        <div class="col-md-4">
            @include('division.partials.division_leadership')
        </div>

    </div>

    <div class="row margin-top-50">
        <div class="col-md-6">
            <div class="panel-body count-detail-big striped-bg">
                <span class="count-animated">{{ $division->members->count() }}</span>
            </div>
        </div>
    </div>

@endsection
