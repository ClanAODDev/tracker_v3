@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            <span class="hidden-xs">Promotions Report</span>
            <span class="visible-xs">Promotions</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('promotions', $division) !!}

        @include ('division.partials.filter-promotions')

        @if ($year && $month && $promotions->count())
            <h4>{{ \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->format('F Y') }} Promotions</h4>
            <hr />
        @elseif ($promotions->count())
            <h4>{{ \Carbon\Carbon::now()->format('F Y') }} Promotions</h4>
            <hr />
        @endif

        @if ($promotions->count())
            @include ('division.partials.member-promotions', ['promotions' => $promotions])
        @else
            <p>No promotions found.</p>
        @endif

    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/division.js'])
@endsection
