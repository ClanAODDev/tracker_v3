@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Census Data
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('division-census', $division) !!}

        @include('division.partials.census-graph')

        @include('division.forms.census')

    </div>

@endsection