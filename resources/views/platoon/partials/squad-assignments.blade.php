@php
    $activityDays = $division->settings()->get('inactivity_days') ?? 30;
    $activityThreshold = now()->subDays($activityDays);
    $unassignedMembers = $platoon->members->filter(
        fn($m) => $m->squad_id == 0 && $m->position === App\Enums\Position::MEMBER
    );
    $canOrganize = auth()->user()->can('update', $platoon);
@endphp

<div class="squad-assignments-section">
    @can('update', $platoon)
        <x-unassigned-organizer
            :count="$unassignedMembers->count()"
            :unit-type="$division->locality('squad')"
            :members="$unassignedMembers"
            button-class="organize-squads-btn"
            members-class="unassigned-squad-member"
            container-class="unassigned-organizer"
            member-id-field="id"
            :can-organize="true"
        />
    @endcan

    <div class="squad-assignments-header">
        <h4 class="squad-assignments-title">
            <i class="fa fa-th-large text-muted"></i>
            {{ Str::plural($division->locality('squad')) }}
        </h4>
        @can('update', $platoon)
            <a href="{{ route('filament.mod.resources.platoons.edit', [$platoon]) }}" class="btn btn-sm btn-default">
                <i class="fa fa-plus text-success"></i> Create {{ $division->locality('squad') }}
            </a>
        @endcan
    </div>

    <div class="squad-drop-targets">
        @foreach($platoon->squads as $squad)
            @php
                $memberCount = $squad->members->count();
                $voiceActiveCount = $squad->members->filter(fn ($m) => $m->last_voice_activity >= $activityThreshold)->count();
                $voiceRate = $memberCount > 0 ? round(($voiceActiveCount / $memberCount) * 100) : 0;
                $voiceClass = $voiceRate >= 50 ? 'voice-high' : ($voiceRate >= 25 ? 'voice-mid' : 'voice-low');
            @endphp
            <a href="{{ route('squad.show', [$division->slug, $platoon, $squad]) }}"
               class="squad-drop-target {{ $canOrganize ? '' : 'squad-drop-target--readonly' }}"
               data-squad-id="{{ $squad->id }}">
                <div class="squad-drop-target-name">
                    {{ $squad->name ?? ordSuffix($loop->iteration) . " Squad" }}
                </div>
                <div class="squad-drop-target-leader">
                    @if($squad->leader)
                        {{ $squad->leader->present()->rankName }}
                    @else
                        <span class="text-muted">TBA</span>
                    @endif
                </div>
                <div class="squad-drop-target-footer">
                    <div class="squad-drop-target-stats">
                        <span class="squad-stat-badge"><i class="fa fa-users"></i> {{ $memberCount }}</span>
                        <span class="squad-stat-badge {{ $voiceClass }}"><i class="fa fa-headset"></i> {{ $voiceRate }}%</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
