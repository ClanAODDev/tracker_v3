@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division, 'logo' => $platoon->getLogoPath()])
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

        @include('platoon.partials.squad-assignments')

        <div class="row">
            <div class="col-lg-10 col-md-9">
                <div class="panel panel-filled ld-loading">
                    <div class="loader">
                        <div class="loader-bar"></div>
                    </div>
                    @include('platoon.partials.platoon-members')
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                @include('member.partials.unit-stats', ['unitStats' => $unitStats])
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection
