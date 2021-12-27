@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', [$division])
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
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

        <p class="text-info">Members should be PMed before they are flagged for removal</p>

        <p><strong>{{ $division->name }}</strong> division inactivity set to
            <code>{{ $division->settings()->inactivity_days }} days</code>
        </p>

        <hr/>

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a data-toggle="tab" href="#inactive"
                       aria-expanded="true">Inactive <span class="badge">{{ count($inactiveMembers) }}</span></a></li>
                <li>
                    <a data-toggle="tab" href="#flagged"
                       aria-expanded="false">Flagged <span class="badge">{{ count($flaggedMembers) }}</span>
                    </a>
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

        @if ($flaggedMembers)
            <div class="panel panel-filled">
                <div class="panel-heading">Share Flagged Members ({{ count($flaggedMembers) }})</div>
                <div class="panel-body">
                    <pre name="bb-code-flagged" id="bb-code-flagged"
                         style="max-height: 100px; overflow-y: scroll">[list]@foreach ($flaggedMembers as $member)
                            [*][profile={{ $member->clan_id }}]{{ $member->present()->rankName }}[/profile] -
                            Seen {{ $member->last_activity->diffInDays() }} days ago @endforeach[/list]</pre>
                    <button data-clipboard-target="#bb-code-flagged" class="copy-to-clipboard btn-success btn"><i
                                class="fa fa-clone"></i> Copy BB-Code
                    </button>
                </div>
            </div>
        @endif

        @include('division.partials.inactivity-log')
    </div>

@endsection
