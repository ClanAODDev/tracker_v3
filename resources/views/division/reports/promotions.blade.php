@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Promotions Report
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('promotions', $division) !!}

        @include('division.partials.select-panel')

        @if ($promotions->count())
            @include('division.partials.member-promotions')
        @else
            @include('division.partials.filter-promotions')
            <div class="report-empty">
                <i class="fa fa-medal"></i>
                <h4>No Promotions Found</h4>
                <p>No promotions were recorded for {{ $periodLabel }}.</p>
            </div>
        @endif

    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/division.js'])
@endsection
