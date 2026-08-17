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
                        <div class="discord-confirm-division-picker">
                            <select class="form-control division-picker-select">
                                <option value="">Select division...</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ route('recruiting.form', $division) }}?pending_user_id={{ $pendingUser['id'] }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-success division-picker-proceed" disabled>
                                Proceed <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @elseif ($memberMatches->isNotEmpty())
            <div class="discord-confirm-not-found">
                <div class="discord-confirm-card">
                    <div class="discord-confirm-header">
                        <i class="fab fa-discord discord-confirm-header-icon"></i>
                        <div class="discord-confirm-identity">
                            <h3>No Pending Discord Application</h3>
                            <div class="discord-confirm-meta">
                                <span><i class="fa fa-hashtag"></i> {{ $discordId }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="discord-confirm-section">
                        <p class="text-muted" style="margin-bottom: 0;">This Discord account isn't tied to a pending registration, but it matches {{ $memberMatches->count() > 1 ? 'these member records' : 'a member record' }} already in the Tracker:</p>
                    </div>

                    @foreach ($memberMatches as $match)
                        <div class="discord-confirm-match">
                            @if ($match['avatarUrl'])
                                <img src="{{ $match['avatarUrl'] }}" alt="{{ $match['name'] }}" class="discord-confirm-match-avatar">
                            @else
                                <div class="discord-confirm-match-avatar discord-confirm-match-avatar-placeholder">
                                    <i class="fa {{ $match['isExMember'] ? 'fa-history' : 'fa-user' }}"></i>
                                </div>
                            @endif
                            <div class="discord-confirm-match-identity">
                                <a href="{{ $match['url'] }}" target="_blank"><strong>{{ $match['name'] }}</strong></a>
                                <div class="text-muted" style="font-size: 12px;">
                                    @if ($match['isExMember'])
                                        <i class="fa fa-history"></i> Former member — no longer in a division
                                    @else
                                        <i class="fa fa-user"></i> Active member of {{ $match['division'] }}
                                    @endif
                                </div>
                            </div>
                            @if ($match['isExMember'])
                                <div class="discord-confirm-match-action">
                                    <div class="discord-confirm-division-picker discord-confirm-division-picker-sm">
                                        <select class="form-control division-picker-select">
                                            <option value="">Recruit into...</option>
                                            @foreach ($divisions as $division)
                                                <option value="{{ route('recruiting.form', $division) }}?member_id={{ $match['clan_id'] }}">{{ $division->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-success division-picker-proceed" disabled>
                                            Proceed <i class="fa fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="discord-confirm-match-action text-muted" style="font-size: 12px; text-align: right;">
                                    Already active — no recruitment needed
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="discord-confirm-footer discord-confirm-footer-split">
                        <a href="{{ route('recruiting.initial') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Recruitment
                        </a>
                        <span class="text-muted" style="font-size: 12px;">
                            None of these? Have the recruit apply via <a href="https://clanaod.net" target="_blank">clanaod.net</a>.
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="discord-confirm-not-found">
                <div class="discord-confirm-card">
                    <div class="discord-confirm-header">
                        <i class="fab fa-discord discord-confirm-header-icon"></i>
                        <div class="discord-confirm-identity">
                            <h3>No Pending Registration Found</h3>
                            <div class="discord-confirm-meta">
                                <span><i class="fa fa-hashtag"></i> {{ $discordId }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="discord-confirm-section">
                        <p class="text-muted" style="margin-bottom: 0;">This Discord account isn't tied to a pending registration or an existing member record. Have the recruit apply to AOD via the website — <a href="https://clanaod.net" target="_blank">clanaod.net</a> — and click "Apply".</p>
                    </div>

                    <div class="discord-confirm-footer">
                        <a href="{{ route('recruiting.initial') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to Recruitment
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('footer_scripts')
<script>
    $(function() {
        $('.discord-confirm-division-picker').each(function() {
            const $picker = $(this);
            const $select = $picker.find('.division-picker-select');
            const $button = $picker.find('.division-picker-proceed');

            $select.on('change', function() {
                $button.prop('disabled', !$select.val());
            });

            $button.on('click', function() {
                if ($select.val()) {
                    window.location.href = $select.val();
                }
            });
        });
    });
</script>
@endsection
