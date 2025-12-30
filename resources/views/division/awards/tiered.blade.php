@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            Awards
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-medal"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            {{ $group['name'] }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('awards.tiered', $group) !!}

        <div class="row m-b-lg">
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $stats->totalRecipients }}</h1>
                        <div class="text-muted">Members with Awards</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-info">{{ $stats->firstAwarded?->format('M Y') ?? '-' }}</span>
                        </h1>
                        <div class="text-muted">First Awarded</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $tiers->count() }}</h1>
                        <div class="text-muted">Tiers</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            @if($stats->earnedCount === $stats->totalTiers)
                                <span class="text-success"><i class="fa fa-check-circle"></i></span>
                            @else
                                <span class="{{ $stats->earnedCount > 0 ? 'text-warning' : 'text-muted' }}">{{ $stats->earnedCount }}/{{ $stats->totalTiers }}</span>
                            @endif
                        </h1>
                        <div class="text-muted">Your Progress</div>
                    </div>
                </div>
            </div>
        </div>

        @if($stats->earnedCount > 0)
            <div class="tiered-progress-bar m-b-lg">
                <div class="progress" style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px;">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ $stats->progressPct }}%; background: var(--color-accent);"
                         aria-valuenow="{{ $stats->progressPct }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="text-center text-muted" style="margin-top: 5px; font-size: 12px;">
                    @if($stats->earnedCount === $stats->totalTiers)
                        <i class="fa fa-trophy text-warning"></i> All tiers completed!
                    @else
                        {{ $stats->earnedCount }} of {{ $stats->totalTiers }} tiers earned
                    @endif
                </div>
            </div>
        @endif

        <div class="panel panel-filled">
            <div class="panel-heading">
                <h4 style="margin:0;"><i class="fa fa-layer-group"></i> {{ $group['name'] }} Progression</h4>
            </div>
            <div class="panel-body">
                <p class="text-muted m-b-lg">{{ $group['description'] ?? 'Progress through the tiers by earning each award in sequence.' }}</p>

                <div class="tenure-progression">
                    @foreach($tiers as $index => $tier)
                        @php
                            $isEarned = in_array($tier->id, $userAwardIds);
                            $isNext = $tier->id === $nextTierId;
                            $rarity = $tier->getRarity();
                            $userAward = $userAwards->get($tier->id);
                            $earnedDate = $userAward?->created_at;
                            $tierPct = $stats->totalRecipients > 0
                                ? round(($tier->recipients_count / $stats->totalRecipients) * 100)
                                : 0;
                        @endphp
                        <a href="{{ route('awards.show', $tier) }}"
                           class="tenure-tier {{ $isEarned ? 'tenure-tier-earned' : 'tenure-tier-locked' }} {{ $isNext ? 'tenure-tier-next' : '' }}">
                            <div class="tenure-tier-connector {{ $index === 0 ? 'tenure-tier-first' : '' }}"></div>
                            <div class="tenure-tier-badge rarity-{{ $rarity }}">
                                @if($tier->image && Storage::disk('public')->exists($tier->image))
                                    <img src="{{ $tier->getImagePath() }}" alt="{{ $tier->name }}" />
                                @else
                                    <div class="tenure-tier-placeholder"><i class="fas fa-trophy"></i></div>
                                @endif
                                @if($isEarned)
                                    <div class="tenure-earned-check"><i class="fa fa-check"></i></div>
                                @elseif($isNext)
                                    <div class="tenure-next-indicator"><i class="fa fa-arrow-right"></i></div>
                                @endif
                            </div>
                            <div class="tenure-tier-info">
                                <h4>
                                    {{ $tier->name }}
                                    @if($isNext)
                                        <span class="label label-primary" style="font-size: 10px; vertical-align: middle;">Next</span>
                                    @endif
                                </h4>
                                <p class="text-muted">{{ $tier->description }}</p>
                                <div class="tenure-tier-stats">
                                    <span class="award-pill pill-{{ $rarity }}">{{ ucfirst($rarity) }}</span>
                                    <span class="text-muted" style="margin-left: 10px;">
                                        <i class="fa fa-users"></i> {{ $tier->recipients_count }} ({{ $tierPct }}%)
                                    </span>
                                    @if($isEarned && $earnedDate)
                                        <span class="text-success" style="margin-left: 10px;">
                                            <i class="fa fa-calendar-check"></i> Earned {{ $earnedDate->format('M j, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="tenure-tier-arrow hidden-xs">
                                <i class="fa fa-chevron-right text-muted"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

@endsection
