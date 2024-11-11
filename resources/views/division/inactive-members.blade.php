@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            <span class="hidden-xs">Inactive Members</span>
            <span class="visible-xs">Inactive</span>
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('inactive-members', $division) !!}

        <p>Members listed here have activity that has reached or exceeded the number of days defined by the division
            leadership. Use this page to attempt to communicate with inactive members, and also to process their removal
            from the clan. Members who have an active leave of absence are omitted.</p>

        <p><strong>{{ $division->name }}</strong> division inactivity set to
            <code>{{ $division->settings()->inactivity_days }} days</code>
        </p>

        <hr/>

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#inactive-discord"
                       aria-expanded="true"><i class="fab fa-lg fa-discord"></i> &nbsp; Discord Inactive
                        <span class="badge">{{ count($inactiveDiscordMembers)}}</span>
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#flagged"
                       aria-expanded="false"><i class="fa fa-flag"></i> &nbsp; Flagged <span
                                class="badge">{{ count($flaggedMembers) }}</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div id="inactive-discord" class="tab-pane active">
                    <div class="panel-body">
                        @include('division.partials.filter-inactive')
                        @include('division.partials.inactive-members', ['type' => 'discord'])
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

@endsection
