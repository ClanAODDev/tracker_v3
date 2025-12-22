@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $squad->name ?? "Untitled " . $division->locality('squad') }}
            @include('squad.partials.edit-squad-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('squad', $division, $platoon, $squad) !!}

        @include('division.partials.select-panel')

        <div class="row">
            <div class="col-md-10">
                <div class="panel panel-filled ld-loading">
                    <div class="loader">
                        <div class="loader-bar"></div>
                    </div>
                    @include('squad.partials.squad-members')
                </div>
            </div>
            <div class="col-md-2">
                @include('platoon.partials.squads')
                @include('squad.partials.member-stats')
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection
