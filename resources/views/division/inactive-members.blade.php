@extends('application.base')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Inactive Members</span>
            <span class="visible-xs">Inactive</span>
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('inactive-members', $division) !!}

        <p>Members listed here have activity that has reached or exceeded the number of days defined by the division leadership. Use this page to attempt to communicate with inactiveMembers members, and also to process their removal from the clan. Members who have an
            <strong>active leave of absence</strong> are <strong>not</strong> listed here.</p>

        <p>{{ $division->name }} division inactivity set to
            <code>{{ $division->settings()->inactivity_days }} days</code>
        </p>

        <hr />

        <div class="panel">
            <div class="panel-body">
                <strong class="c-white">
                    @if ($queryingTsInactives)
                        Teamspeak Inactivity
                    @else
                        Forum Inactivity
                    @endif
                </strong>
                <span class="pull-right">
                    Filter by:
                    <a href="{{ route('division.inactive-members', $division->abbreviation) }}"
                       class="btn btn-info {{ set_active('divisions/*/inactive-members') }}">By Forum Activity</a>
                    <a href="{{ route('division.inactive-members-ts', $division->abbreviation) }}"
                       class="btn btn-info {{ set_active('divisions/*/inactive-members-ts') }}">By TS Activity</a>
                </span>
            </div>
        </div>

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#inactive"
                       aria-expanded="true"> Inactive <span class="badge">{{ count($inactiveMembers) }}</span></a></li>
                <li>
                    <a data-toggle="tab" href="#flagged"
                       aria-expanded="false">Flagged <span class="badge">{{ count($flaggedMembers) }}</span>
                    </a></li>
            </ul>
            <div class="tab-content">
                <div id="inactive" class="tab-pane active">
                    <div class="panel-body">
                        @include('division.partials.filter-inactive')
                        @include('division.partials.inactive-members')
                    </div>
                </div>
                <div id="flagged" class="tab-pane">
                    <div class="panel-body">
                        @include('division.partials.flagged-members')
                    </div>
                </div>
            </div>
        </div>

        @include('division.partials.inactivity-log')
    </div>
@stop
