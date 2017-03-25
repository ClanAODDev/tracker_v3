@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @endslot
        @slot ('heading')
            {{ $division->name }} Division
            @include('application.partials.editDivision', ['division' => $division])
        @endslot
        @slot ('subheading')
            Census data and statistics
        @endslot
    @endcomponent

@stop