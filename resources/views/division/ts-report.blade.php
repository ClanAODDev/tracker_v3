@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
        @endslot
        @slot ('heading')
            Teamspeak Issues
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('teamspeak-report', $division) !!}

        <p>Below is a report of your division members' Teamspeak Unique IDs. If you have members listed below, it is important to get with those individuals and resolve these issues as soon as possible.</p>

        @if (count($mismatches))
            <h4 class="m-t-xl"><i class="fa fa-exclamation-triangle text-danger"></i> Mismatched TS Unique IDs <span
                        class="pull-right text-muted">{{ count($mismatches) }} Issues</span></h4>
            <hr />
            <div class="panel panel-filled">
                <div class="panel-body">
                    <p>
                        <strong class="text-accent">The problem: </strong> This occurs when the forum member profile unique id does not match what the user last logged in with. Ensure the member doesn't have multiple identities. SGTs+ can verify these values match through the ModCP and on TS via
                        <code>Permissions > Channel Groups Of Client</code></p>
                </div>
            </div>

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>Forum Profile</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($mismatches as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->clan_id) }}"><i class="fa fa-search"></i></a>
                            {{ $member->present()->rankName }}
                        </td>
                        <td>
                            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}">{{ $member->clan_id }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if (count($nulls))
            <h4 class="m-t-xl">
                <i class="fa fa-exclamation-triangle text-danger"></i>
                Null TS Unique IDs <span class="pull-right text-muted">{{ count($nulls) }} Issues</span>
            </h4>

            <hr />
            <div class="panel panel-filled">
                <div class="panel-body">
                    <p>
                        <strong class="text-accent">The problem: </strong> This occurs when a member does not have a unique id stored in the forum profile. To fix, reach out to the member and have them properly complete their TS unique id information on the forums.
                    </p>
                </div>
            </div>

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>Forum Profile</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($nulls as $member)
                    <tr>
                        <td>{{ $member->present()->rankName }}</td>
                        <td>
                            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}">
                                {{ $member->clan_id }}
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

    </div>

@stop