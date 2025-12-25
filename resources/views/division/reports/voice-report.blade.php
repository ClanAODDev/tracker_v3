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

        @include('division.partials.select-panel')

        <div class="report-stats">
            <div class="report-stat report-stat--primary">
                <div class="report-stat-value">{{ $stats['total'] }}</div>
                <div class="report-stat-label">Total Issues</div>
            </div>
            <div class="report-stat report-stat--danger">
                <div class="report-stat-value">{{ $stats['disconnected'] }}</div>
                <div class="report-stat-label">Disconnected</div>
            </div>
            <div class="report-stat report-stat--muted">
                <div class="report-stat-value">{{ $stats['neverConnected'] }}</div>
                <div class="report-stat-label">Never Connected</div>
            </div>
            <div class="report-stat report-stat--warning">
                <div class="report-stat-value">{{ $stats['neverConfigured'] }}</div>
                <div class="report-stat-label">Never Configured</div>
            </div>
        </div>

        @if($stats['total'] > 0)
            <div class="voice-legend">
                <div class="voice-legend-item">
                    <span class="voice-legend-dot voice-legend-dot--danger"></span>
                    <strong>Disconnected</strong>: Was connected but no longer linked
                </div>
                <div class="voice-legend-item">
                    <span class="voice-legend-dot voice-legend-dot--muted"></span>
                    <strong>Never Connected</strong>: Has never joined AOD Discord
                </div>
                <div class="voice-legend-item">
                    <span class="voice-legend-dot voice-legend-dot--warning"></span>
                    <strong>Never Configured</strong>: No Discord info on AOD profile
                </div>
            </div>

            <div class="voice-table-container">
                <table class="table table-hover members-table voice-table">
                    <thead>
                    <tr>
                        <th>Member</th>
                        <th>Status</th>
                        <th>{{ $division->locality('platoon') }}</th>
                        <th>Discord</th>
                        <th>Last Activity</th>
                        <th>Forum</th>
                        <th class="col-hidden">Clan ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($discordIssues as $member)
                        <tr class="voice-row voice-row--{{ $member->last_voice_status->value }}">
                            <td>
                                <a href="{{ route('member', $member->getUrlParams()) }}" class="voice-member-link">
                                    <i class="fa fa-search"></i>
                                    {{ $member->present()->rankName }}
                                </a>
                            </td>
                            <td>
                                @include('division.partials.voice-status', ['status' => $member->last_voice_status])
                            </td>
                            <td>{{ $member->platoon?->name ?? '—' }}</td>
                            <td class="voice-discord">{{ $member->discord ?? '—' }}</td>
                            <td class="voice-activity">{{ $member->present()->lastActive('last_voice_activity') }}</td>
                            <td>
                                <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}" target="_blank" class="voice-forum-link">
                                    <i class="fa fa-external-link-alt"></i>
                                </a>
                            </td>
                            <td class="col-hidden">{{ $member->clan_id }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="report-empty">
                <i class="fab fa-discord"></i>
                <h4>No Issues Found</h4>
                <p>All members in {{ $division->name }} have properly configured Discord.</p>
            </div>
        @endif

    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/voice.js'])
@endsection
