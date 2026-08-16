@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Confirm Discord Recruit
        @endslot
        @slot ('icon')
            <img src="{{ getThemedLogoPath() }}" width="50px"/>
        @endslot
        @slot ('heading')
            Confirm Discord Recruit
        @endslot
        @slot ('subheading')
            Verify identity before starting recruitment
        @endslot
    @endcomponent

    <div class="container-fluid">
        @if ($pendingUser)
            <div class="discord-confirm-card">
                <div class="discord-confirm-header">
                    <img src="{{ $pendingUser['avatar_url'] }}" alt="{{ $pendingUser['discord_username'] }}" class="discord-confirm-avatar">
                    <div class="discord-confirm-identity">
                        <h3>{{ $pendingUser['discord_username'] }}</h3>
                        <div class="discord-confirm-meta">
                            <span><i class="fa fa-clock-o"></i> Applied {{ $pendingUser['created_at'] }}</span>
                            @if ($pendingUser['obfuscated_email'])
                                <span><i class="fa fa-envelope"></i> {{ $pendingUser['obfuscated_email'] }}</span>
                            @endif
                        </div>
                    </div>
                    @if ($targetDivision)
                        <div class="discord-confirm-division-badge">
                            <img src="{{ $targetDivision->getLogoPath() }}" class="discord-confirm-division-icon" alt="{{ $targetDivision->name }}">
                            {{ $targetDivision->name }} Division
                        </div>
                    @endif
                </div>

                <div class="discord-confirm-section">
                    @if ($forumAccount['found'] ?? false)
                        @if ($forumAccount['eligible'])
                            <div class="recruit-status-line recruit-status-line-success">
                                <i class="fa fa-check-circle"></i>
                                <div>Forum account found: <strong>{{ $forumAccount['username'] }}</strong> (ID: {{ $forumAccount['user_id'] }})</div>
                            </div>
                        @else
                            <div class="recruit-email-check-status recruit-email-check-blocked">
                                <i class="fa fa-exclamation-triangle"></i>
                                <div>
                                    <div>A forum account was found: <strong>{{ $forumAccount['username'] }}</strong></div>
                                    <div class="text-muted" style="font-size: 12px; margin-top: 2px;">{{ $forumAccount['rejection_reason'] }}</div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="recruit-status-line recruit-status-line-info">
                            <i class="fa fa-info-circle"></i>
                            <div>No existing forum account found. One will be created during recruitment.</div>
                        </div>
                    @endif
                </div>

                @if ($pendingUser['application'])
                    <div class="discord-confirm-responses">
                        <h5 class="discord-confirm-responses-title">Application Responses</h5>
                        <div class="row">
                            @foreach ($pendingUser['application'] as $item)
                                <div class="col-md-6 application-field">
                                    <div class="application-field-label">{{ $item['label'] }}</div>
                                    <div class="application-field-value">{{ $item['value'] ?: '—' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($targetDivision)
                    <div class="discord-confirm-footer">
                        <a href="{{ route('recruiting.form', $targetDivision) }}?pending_user_id={{ $pendingUser['id'] }}" class="btn btn-success">
                            Start Recruitment <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="discord-confirm-section">
                        <p class="text-muted" style="margin-bottom: 10px;">No division application on file — select which division to recruit them into:</p>
                        <div class="row">
                            @foreach ($divisions as $division)
                                <div class="col-sm-4" style="margin-bottom: 10px;">
                                    <a href="{{ route('recruiting.form', $division) }}?pending_user_id={{ $pendingUser['id'] }}" class="panel panel-filled" style="margin-bottom: 0;">
                                        <div class="panel-body">
                                            <h5 class="m-b-none">
                                                <img src="{{ $division->getLogoPath() }}" class="division-icon-medium">
                                                {{ $division->name }}
                                            </h5>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="discord-confirm-empty">
                <i class="fab fa-discord discord-confirm-empty-icon"></i>
                <h4>No Pending Registration Found</h4>
                <p class="text-muted">There's no pending Discord registration for this account (ID: <code>{{ $discordId }}</code>). Have the recruit apply to AOD via the website — <a href="https://clanaod.net" target="_blank">clanaod.net</a> — and click "Apply".</p>
                <a href="{{ route('recruiting.initial') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back to Recruitment
                </a>
            </div>
        @endif
    </div>
@endsection
