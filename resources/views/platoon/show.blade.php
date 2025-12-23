@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $platoon->name ?? "Untitled " . $division->locality('platoon') }}
            @include('platoon.partials.edit-platoon-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        @include('division.partials.select-panel')

        @include('platoon.partials.notices')

        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-filled ld-loading">
                    <div class="loader">
                        <div class="loader-bar"></div>
                    </div>
                    @include('platoon.partials.platoon-members')
                </div>
            </div>
            <div class="col-md-2">
                @include('platoon.partials.squads')
                @include('member.partials.unit-stats', ['members' => $members, 'voiceActivityGraph' => $voiceActivityGraph, 'division' => $division])
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection
