@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Member Requests</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member-requests', $division) !!}

        <p>Below are member requests submitted to the clan leadership for approval. </p>

        @include('division.partials.pending-requests')

        <hr>
        @include('division.partials.denied-requests')

    </div>

@stop
