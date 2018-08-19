@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="text-uppercase">Member Requests</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-requests', $division) !!}

        <h3>Division Member Requests</h3>

        <p>Below are member requests submitted to the clan leadership for approval. Cancelled requests can be resubmitted once changes are made.</p>

        <hr />

        @include('division.partials.pending-requests')
        @include('division.partials.cancelled-requests')

    </div>

@stop
