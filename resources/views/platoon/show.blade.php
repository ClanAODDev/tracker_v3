@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->slug) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
            </a>
        @endslot
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
                @include('platoon.partials.member_stats')
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2.2') !!}"></script>
@endsection
