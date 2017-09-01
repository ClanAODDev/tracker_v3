@extends('application.base')
@section('content')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            <i class="fa fa-slack"></i> Manage Slack Users
        @endslot
    @endcomponent

    <div class="container-fluid">
        @unless (auth()->user()->isRole('sr_ldr'))
            <div class="alert alert-warning">
                There are
                <code>{{ $matchingCount - count($users) }}</code> accounts unlinked with Slack, or emails that do not correspond to a user on Slack
            </div>
        @endunless

        <div class="panel panel-filled m-b-lg">
            <div class="panel-body">
                <p>The following users are listed as having accounts on Slack that match a user account on the tracker. If a Slack user for your division is not listed, they may have registered with a different email. Encourage members to use the same email for forum, slack, and tracker accounts.</p>
            </div>
        </div>

        <table class="table table-hover adv-datatable">
            <thead>
            <tr>
                <th>Name</th>
                <th>Rank</th>
                <th>Position</th>
                <th>Forum Activity</th>
            </tr>
            </thead>
            @foreach ($users as $user)
                <tr>
                    <td>
                        {{ $user->name }} <span class="text-muted">{{ $user->member->rank->abbreviation }}</span>
                        <a href="{{ route('member', $user->member->clan_id) }}" class="pull-right">
                            <i class="fa fa-search text-accent"></i>
                        </a>
                    </td>
                    <td>{{ $user->member->rank_id }}</td>
                    <td>{{ $user->member->position->name }}</td>
                    <td title="{{ $user->member->last_activity->diffForHumans() }}">
                        {{ $user->member->last_activity }}
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

@stop