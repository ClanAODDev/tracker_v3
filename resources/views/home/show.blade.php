@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ getThemedLogoPath() }}" class="division-icon-large" />
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
            <h3 class="division-section-title">All <span class="text-muted">Divisions</span></h3>
            <hr/>
            @include('home.partials.divisions')
        </div>
    </div>
@endsection
