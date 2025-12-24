@php
    $activityDays = $division->settings()->get('inactivity_days') ?? 30;
    $activityThreshold = now()->subDays($activityDays);
@endphp
<div class="panel">
    @foreach ($platoon->squads as $squad)
        @php
            $memberCount = $squad->members->count();
            $voiceActiveCount = $squad->members->filter(fn ($m) => $m->last_voice_activity >= $activityThreshold)->count();
            $voiceRate = $memberCount > 0 ? round(($voiceActiveCount / $memberCount) * 100) : 0;
            $voiceClass = $voiceRate >= 50 ? 'voice-high' : ($voiceRate >= 25 ? 'voice-mid' : 'voice-low');
        @endphp
        <a class="list-group-item"
           href="{{ route('squad.show', [$division->slug, $platoon, $squad]) }}">
            {{ $squad->name ?? ordSuffix($loop->iteration) . " Squad" }}<br/>
            <span class="slight text-muted">
                @if ($squad->leader)
                    {{ $squad->leader->present()->rankName }}
                @else
                    TBA
                @endif
            </span>
            <div class="squad-stats">
                <span class="squad-stat-badge"><i class="fa fa-users"></i> {{ $memberCount }}</span>
                <span class="squad-stat-badge {{ $voiceClass }}"><i class="fa fa-headset"></i> {{ $voiceRate }}%</span>
            </div>
        </a>
    @endforeach

    @can('update', $platoon)
        <a href="{{ route('filament.mod.resources.platoons.edit', [$platoon]) }}" class="list-group-item">
            <i class="fa fa-plus pull-right text-success"></i> Create {{ $division->locality('squad') }}
        </a>
    @endcan

</div>