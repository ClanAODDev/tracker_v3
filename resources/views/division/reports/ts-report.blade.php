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

        <div class="panel panel-filled m-t-xl">
            <div class="panel-heading">
                Potential Problems
            </div>
            <div class="panel-body">
                <p>
                    <strong class="text-accent">Mismatched Unique IDs</strong> This occurs when the forum member profile unique id does not match what the user last logged in with. Ensure the member doesn't have multiple identities. SGTs+ can verify these values match through the ModCP and on TS via
                    <code>Permissions > Channel Groups Of Client</code>
                </p>

                <p>
                    <strong class="text-accent">Null Unique IDs: </strong> This occurs when a member does not have a unique id stored in the forum profile. To fix, reach out to the member and have them properly complete their TS unique id information on the forums.
                </p>
            </div>
        </div>

        @if (count($issues))
            <h4 class="m-t-xl">
                <i class="fa fa-exclamation-triangle text-danger"></i> Misconfigured TS Unique IDs
                <span class="pull-right text-muted">{{ count($issues) }} Issues</span>
            </h4>
            <hr />

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>Last Forum Activity</th>
                    <th>Forum Profile</th>
                    @if (auth()->user()->isRole(['sr_ldr', 'admin']))
                        <th>TS ID (forums)</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach ($issues as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                            {{ $member->present()->rankName }}
                        </td>
                        <td>
                            {{ $member->last_activity->diffForHumans() }}
                        </td>
                        <td>
                            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}">
                                <i class="fa fa-link"></i> {{ $member->clan_id }}
                            </a>
                        </td>
                        @if (auth()->user()->isRole(['sr_ldr', 'admin']))
                            <td><code>{{ $member->ts_unique_id ?: "None set" }}</code></td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>Congratulations, you have no teamspeak misconfiguration issues!</p>
        @endif

    </div>

@stop