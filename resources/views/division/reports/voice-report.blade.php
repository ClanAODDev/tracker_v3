@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Voice Comms Issues
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('voice-report', $division) !!}

        @if(!count($discordIssues))
            <p>There no voice comms issues to resolve for the {{ $division->name }} division.</p>
        @endif

        @if (count($discordIssues))
            <h4 class="m-t-lg">
                <i class="fab fa-discord fa-lg text-danger"></i> Discord Issues
                <span class="pull-right text-muted">{{ count($discordIssues) }} Issue(s)</span>
            </h4>

        <p>Select 2 or more members to start a mass PM. Tip: Hold CTRL to multi-select.</p>

            @include('division.partials.select-panel')

            <table class="table table-hover members-table">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>State</th>
                    <th>Forum Profile</th>
                    <th>{{ $division->locality('platoon') }}</th>
                    <th>Discord</th>
                    <th>Last VoIP Activity</th>
                    <th class="col-hidden">Clan ID</th>
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
                            {{ $member->platoon?->name }}
                        </td>
                        <td>
                            {{ $member->discord }}
                        </td>
                        <td>
                            {{-- temporary handling of null dates --}}
                            {{ $member->present()->lastActive('last_voice_activity') }}
                        </td>
                        <td class="col-hidden">
                            {{ $member->clan_id }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if(count($discordIssues))
            <div class="panel panel-filled m-t-sm">
                <div class="panel-body">
                    <h4 class="pt-sm">Discord states</h4>
                    <p><strong class="text-danger">Disconnected</strong>: User was connected but not anymore.</p>
                    <p><strong class="text-muted">Never Connected</strong>: User has never connected to the AOD
                        Discord.</p>
                    <p><strong class="text-warning">Never Configured</strong>: User has not provided Discord
                        information to
                        AOD.</p>
                </div>
            </div>
        @endif
    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/voice.js'])
@endsection