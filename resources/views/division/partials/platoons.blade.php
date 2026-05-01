<div class="division-section platoon-assignments-section animate-fade-in-up" style="animation-delay: 0.35s" id="platoons">

@can('manageUnassigned', App\Models\User::class)
    @include('division.partials.unassigned-members')
@endcan

<div class="division-section-title">
    {{ Str::plural($division->locality('platoon')) }}

    @can('create', [App\Models\Platoon::class, $division])
        <a href="{{ route('filament.mod.resources.divisions.edit', $division) }}"
           class="btn btn-default pull-right"><i class="fa fa-plus text-success"></i> Create {{ $division->locality('platoon') }}
        </a>
    @endcan
</div>
<hr/>

<div class="row platoon-drop-targets">

    @forelse ($platoons as $platoon)
        @php
            $voiceRate = $platoon->members_count > 0
                ? round(($platoon->voice_active_count / $platoon->members_count) * 100)
                : 0;
            $voiceClass = $voiceRate >= 30 ? 'voice-high' : ($voiceRate >= 15 ? 'voice-mid' : 'voice-low');
        @endphp
        <div class="col-xs-12 col-md-6">
            <a href="{{ route('platoon', [$division->slug, $platoon->id]) }}"
               class="panel panel-filled platoon w-100 {{ $voiceClass }}" data-platoon-id="{{ $platoon->id }}">
                <div class="panel-body platoon-card-body">
                    <div class="platoon-section platoon-section-header">
                        @if ($platoon->logo)
                            <img src="{{ $platoon->logo }}" class="platoon-logo-avatar"/>
                        @endif
                        <div class="platoon-header-text">
                            <h4 class="m-b-none platoon-header-name">{{ $platoon->name }}</h4>
                            <div class="platoon-header-leader">
                                @if ($platoon->leader)
                                    @php $avatar = $platoon->leader->getDiscordAvatarUrl(); @endphp
                                    @if ($avatar)
                                        <img src="{{ $avatar }}" class="leader-avatar leader-avatar-sm" alt="{{ $platoon->leader->name }}" />
                                    @else
                                        <span class="rank-dot rank-dot-sm" style="background-color: {{ $platoon->leader->rank->getColorHex() }}"></span>
                                    @endif
                                    {{ $platoon->leader->present()->rankName }}
                                @else
                                    <span class="text-muted">No leader</span>
                                @endif
                            </div>
                            @if ($platoon->description)
                                <p class="platoon-description m-b-none">{{ $platoon->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="platoon-section platoon-section-squads">
                        <div class="platoon-squads-grid">
                            @foreach ($platoon->squads as $squad)
                                @php
                                    $squadVoiceRate = $squad->members_count > 0
                                        ? round(($squad->voice_active_count / $squad->members_count) * 100)
                                        : 0;
                                    $squadVoiceClass = $squadVoiceRate >= 30 ? 'voice-high' : ($squadVoiceRate >= 15 ? 'voice-mid' : 'voice-low');
                                @endphp
                                <div class="squad-card {{ !$squad->leader ? 'squad-card--vacant' : '' }}"
                                     data-squad-id="{{ $squad->id }}"
                                     @if ($squad->leader) style="--rank-color: {{ $squad->leader->rank->getColorHex() }}" @endif>
                                    <div class="squad-name">{{ $squad->name }}</div>
                                    <div class="squad-leader">
                                        @if ($squad->leader)
                                            @php $squadAvatar = $squad->leader->getDiscordAvatarUrl(); @endphp
                                            @if ($squadAvatar)
                                                <img src="{{ $squadAvatar }}" class="leader-avatar leader-avatar-sm" alt="{{ $squad->leader->name }}" />
                                            @else
                                                <span class="rank-dot rank-dot-sm" style="background-color: {{ $squad->leader->rank->getColorHex() }}"></span>
                                            @endif
                                            {{ $squad->leader->present()->rankName }}
                                        @else
                                            <span class="text-muted">TBA</span>
                                        @endif
                                    </div>
                                    <div class="squad-meta">
                                        <span class="squad-stat-badge {{ $squadVoiceClass }}">{{ $squad->members_count }} mbr &middot; {{ $squadVoiceRate }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="platoon-section platoon-section-stats {{ $voiceClass }}" style="--voice-rate: {{ $voiceRate }}%">
                        <div class="platoon-stat {{ $voiceClass }}" title="Voice active ({{ $stats->activityThresholdDays }} days)">
                            <span class="platoon-stat-value">{{ $voiceRate }}%</span>
                            <span class="platoon-stat-label"><span class="voice-status-dot {{ $voiceClass }}"></span>Voice</span>
                        </div>
                        <div class="platoon-stat">
                            <span class="platoon-stat-value">{{ $platoon->members_count }}</span>
                            <span class="platoon-stat-label">Members</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-danger text-muted">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No {{ Str::plural($division->locality('platoon')) }} Found
                    </h4>
                </div>
            </div>
        </div>
    @endforelse
</div>
</div>
