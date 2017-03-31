@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @endslot
        @slot ('heading')
            {{ $division->name }} Division
            @include('division.partials.edit-division-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            Census data and statistics
        @endslot
    @endcomponent

    {!! Breadcrumbs::render('division-census', $division) !!}

    @include('division.partials.census-graph')

    @include('division.forms.census')

@stop