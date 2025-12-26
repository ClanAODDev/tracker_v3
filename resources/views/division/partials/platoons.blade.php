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
        <div class="col-md-6">
            <a href="{{ route('platoon', [$division->slug, $platoon->id]) }}"
               class="panel panel-filled platoon" data-platoon-id="{{ $platoon->id }}">
                <div class="panel-body">

                    <h4 class="m-b-none m-t-sm">
                        @if ($platoon->logo)
                            <img src="{{ $platoon->logo }}"
                                 class="pull-right platoon-icon-xl"/>
                        @endif
                        {{ $platoon->name }}
                    </h4>

                    @if ($platoon->leader)
                        <p class="list-group-item-text">
                            {{ $platoon->leader->present()->rankName }}
                        </p>
                    @else
                        <p class="list-group-item-text">TBA</p>
                    @endif

                    <div class="m-t-lg">
                        @foreach ($platoon->squads as $squad)
                            <div class="squad label label-default m-2"
                                 style="margin-right:15px;"
                                 data-squad-id="{{ $squad->id }}"
                            >
                                {{ $squad->leader ? $squad->leader->present()->rankName : "TBA" }}
                            </div>
                        @endforeach
                    </div>

                    <div class="platoon-card-footer">
                        <div class="platoon-card-stats">
                            <span class="platoon-stat-badge"><i class="fa fa-users"></i> {{ $platoon->members_count }}</span>
                            <span class="platoon-stat-badge {{ $voiceClass }}" title="Voice active ({{ $stats->activityThresholdDays }} days)">
                                <i class="fa fa-headset"></i> {{ $voiceRate }}%
                            </span>
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
