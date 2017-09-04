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

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#inactive"
                       aria-expanded="true"> Inactive <span class="badge">{{ count($inactiveMembers) }}</span></a></li>
                <li>
                    <a data-toggle="tab" href="#flagged"
                       aria-expanded="false">Flagged <span class="badge">{{ count($flaggedMembers) }}</span>
                    </a>
                </li>
                <li class="text-center">
                </li>
                <li class="pull-right">
                    <span class="btn-group-sm btn-group">
                    <a href="{{ route('division.inactive-members', $division->abbreviation) }}"
                       class="btn btn-default {{ set_active(['divisions/*/inactive-members/*', 'divisions/*/inactive-members']) }}">Filter By Forum Activity</a>
                    <a href="{{ route('division.inactive-members-ts', $division->abbreviation) }}"
                       class="btn btn-default {{ set_active(['divisions/*/inactive-members-ts/*', 'divisions/*/inactive-members-ts']) }}">Filter By TS Activity</a>
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#mass-pm-modal">
                        <i class="fa fa-bullhorn text-accent"></i> <span
                                class="hidden-xs hidden-sm">Mass PM Inactives</span>
                    </button>
                        </span>
                </li>
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

    @component('application.components.modal', ['showSaveButton' => false])
        @slot('title')
            Mass Forum PM ({{ count($inactiveMembers) }})
        @endslot
        @slot('body')
            <p>The Clan AOD forums has a maximum number of 20 recipients per PM. To assist with this limitation, members have been chunked into groups for your convenience.</p>
            <p class="m-t-md">
                @foreach ($inactiveMembers->chunk(20) as $chunk)
                    <a href="{{ doForumFunction($chunk->pluck('clan_id')->toArray(), 'pm') }}"
                       target="_blank" class="btn btn-default">
                        <i class="fa fa-link text-accent"></i> Group {{ $loop->iteration }}
                    </a>
                @endforeach
            </p>
        @endslot
    @endcomponent
@stop
