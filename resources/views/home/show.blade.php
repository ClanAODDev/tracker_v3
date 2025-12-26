@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset(config('aod.logo')) }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid home-container">
        @include('home.partials.my-division')
        @include('home.partials.pending-actions')

        <div class="leaderboard-section">
            @include('home.partials.division-leaderboard')
        </div>

        <div class="divisions-section animate-fade-in-up" style="animation-delay: 0.4s">
            <h4 class="section-title">
                <i class="fa fa-gamepad"></i>
                All Divisions
            </h4>
            @include('home.partials.divisions')
        </div>
    </div>
@endsection
