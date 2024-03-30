@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large"/>
        @endslot
        @slot ('heading')
            Voice Comms Issues
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('voice-report', $division) !!}

        @if (count($discordIssues))
            <h4 class="m-t-xl">
                <i class="fa fa-exclamation-triangle text-danger"></i> Discord Issues
                <span class="pull-right text-muted">{{ count($discordIssues) }} Issue(s)</span>
            </h4>
            <hr/>

            <table class="table table-hover basic-datatable">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>State</th>
                    <th>Forum Profile</th>
                    <th>{{ $division->locality('platoon') }}</th>
                    <th>Last Activity</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($discordIssues as $member)
                    <tr>
                        <td>
                            <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                            {{ $member->present()->rankName }}
                        </td>
                        <td>
                            @include('division.partials.voice-status', ['status' =>  $member->last_voice_status])
                        </td>
                        <td>
                            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}" target="_blank">
                                <i class="fa fa-link"></i> {{ $member->clan_id }}
                            </a>
                        </td>
                        <td>
                            {{ $member->platoon->name }}
                        </td>
                        <td>
                            {{-- temporary handling of null dates --}}
                            @if(str_contains($member->last_voice_activity, '1970'))
                                Never
                            @else
                                {{ $member->last_voice_activity }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if (count($tsIssues))
        <h4 class="m-t-xl">
            <i class="fa fa-exclamation-triangle text-danger"></i> Teamspeak Issues
            <span class="pull-right text-muted">{{ count($tsIssues) }} Issue(s)</span>
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
            @foreach ($tsIssues as $member)
                <tr>
                    <td>
                        <a href="{{ route('member', $member->getUrlParams()) }}"><i class="fa fa-search"></i></a>
                        {{ $member->present()->rankName }}
                    </td>
                    <td>
                        {{ $member->last_activity->diffForHumans() }}
                    </td>
                    <td>
                        <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}"  target="_blank">
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

        <div class="panel panel-filled m-t-xl">
            <div class="panel-body">
                <div class="col-sm-12">
                    <div class="col-sm-6">
                        <h4>Teamspeak Issues</h4>
                        <p>
                            <strong class="text-accent">Mismatched Unique IDs</strong> This occurs when the forum member profile unique id does not match what the user last logged in with. Ensure the member doesn't have multiple identities. SGTs+ can verify these values match through the ModCP and on TS via
                            <code>Permissions > Channel Groups Of Client</code>
                        </p>

                        <p>
                            <strong class="text-accent">Null Unique IDs: </strong> This occurs when a member does not have a unique id stored in the forum profile. To fix, reach out to the member and have them properly complete their TS unique id information on the forums.
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <h4>Discord states</h4>
                        <p><strong class="text-danger">Disconnected</strong>: User was connected but not anymore.</p>
                        <p> <strong class="text-muted">Never Connected</strong>: User has never connected to the AOD Discord.</p>
                        <p> <strong class="text-warning">Never Configured</strong>: User has not provided Discord information to AOD.</p>
                    </div>

                </div>


            </div>
        </div>
    @endif


@endsection