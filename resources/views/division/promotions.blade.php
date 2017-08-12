@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
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

        @if ($year && $month && count($members))
            <h4>{{ $month }} {{ $year }} Promotions</h4>
            <hr />
        @endif

        @if (count($members))
            @include ('division.partials.member-promotions')
        @else
            <p>No promotions found.</p>
        @endif

    </div>
@stop
