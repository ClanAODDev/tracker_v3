@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Member Retention
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('retention-report', $division) !!}

        <p>Below is a report of your division members' Teamspeak Unique IDs. If you have members listed below, it is important to get with those individuals and resolve these issues as soon as possible.</p>

        @foreach ($activities as $activity)
            {{ dump($activity) }} <br />
        @endforeach
    </div>

@stop