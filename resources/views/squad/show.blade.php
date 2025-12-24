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
            <div class="col-lg-10 col-md-9">
                <div class="panel panel-filled ld-loading">
                    <div class="loader">
                        <div class="loader-bar"></div>
                    </div>
                    @include('squad.partials.squad-members')
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                @include('platoon.partials.squads')
                @include('member.partials.unit-stats', ['unitStats' => $unitStats])
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection
