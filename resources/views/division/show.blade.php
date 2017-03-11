@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
        @endslot
        @slot ('heading')
            {{ $division->name }} Division
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                @include('division.partials.leadership')
            </div>
        </div>

        @include ('division.partials.census-short')

        <div class="row">
            @include('division.partials.platoons')
        </div>

    </div>

@stop
