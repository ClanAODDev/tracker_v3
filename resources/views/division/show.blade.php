@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
            @include('division.partials.edit-division-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('division.partials.notices')
        @include('division.partials.tools-links')
        <div class="m-b-xl"></div>

        @include ('division.partials.census-short')
        @include('division.partials.leadership')
        @include('division.partials.staff-sergeants')
        @include('division.partials.platoons')
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/division.js?v=1.3') !!}"></script>
@stop
