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
                <li class="active"><a data-toggle="tab" href="#inactive" aria-expanded="true"> Inactive</a></li>
                <li><a data-toggle="tab" href="#flagged" aria-expanded="false">Flagged</a></li>
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

        @if(count($flagActivity))
            <hr />
            <div class="panel panel-filled panel-c-accent">
                <div class="panel-heading">
                    Activity Log
                </div>
                <div class="panel-body">
                    <table class="table table-hover adv-datatable">
                        <thead>
                        <tr>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($flagActivity as $activity)
                            @if (isset($activity->subject->name))
                                @include('division.partials.inactive-activity-log')
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@stop
