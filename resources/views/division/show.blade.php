@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
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
        @include('division.partials.anniversaries')
        @include('division.partials.platoons')

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/division.js'])
@endsection
