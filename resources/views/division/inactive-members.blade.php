@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Inactive Members</span>
            <span class="visible-xs">{{ $division->abbreviation }}</span>
        @endslot
        @slot ('subheading')
            {{ $division->description }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('inactive-members', $division) !!}

        <p>Members listed here have activity that has reached or exceeded the number of days defined by the division leadership. Use this page to attempt to communicate with inactive members, and also to process their removal from the clan. Members who have an <strong>active leave of absence</strong> are <strong>not</strong> listed here.</p>

        <p>{{ $division->name }} division inactivity set to
            <code>{{ $division->settings()->inactivity_days }} days</code>
        </p>

        <hr />

        @include('division.partials.filter-inactive')
        @include('division.partials.inactive-members')
        @include('division.partials.flagged-members')
    </div>
@stop
