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
            Achievements & Awards
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('awards.index') !!}

        <div class="awards-stat-bar m-b-lg">
            <div class="awards-stat">
                <span class="awards-stat-value">{{ $totals->awards }}</span>
                <span class="awards-stat-label">Total Awards</span>
            </div>
            <div class="awards-stat-divider"></div>
            <div class="awards-stat">
                <span class="awards-stat-value text-warning">{{ number_format($totals->recipients) }}</span>
                <span class="awards-stat-label">Times Awarded</span>
            </div>
            <div class="awards-stat-divider"></div>
            <div class="awards-stat">
                <span class="awards-stat-value text-success">{{ $totals->requestable }}</span>
                <span class="awards-stat-label">Requestable</span>
            </div>
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <div class="award-filters">

                    <div class="award-filter-group">
                        <span class="filter-label">Rarity</span>
                        <div class="rarity-legend">
                            @foreach (config('aod.awards.rarity') as $key => $rarity)
                                <div class="rarity-legend-item rarity-filter rarity-{{ $key }}-filter active" data-rarity="{{ $key }}">
                                    <span class="rarity-dot rarity-{{ $key }}"></span>
                                    <span class="rarity-label">{{ $rarity['label'] }}</span>
                                    <span class="rarity-range">
                                        @if ($rarity['max'] === null)
                                            {{ $rarity['min'] }}+
                                        @elseif ($rarity['min'] === $rarity['max'])
                                            {{ $rarity['min'] }} {{ Str::plural('recipient', $rarity['min']) }}
                                        @else
                                            {{ $rarity['min'] }}-{{ $rarity['max'] }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="award-filter-group">
                        <span class="filter-label">Division</span>
                        <select id="division-filter" class="form-control" style="width: auto;">
                            <option value="">All Divisions</option>
                            @foreach ($divisionsWithAwards as $division)
                                <option value="{{ $division->slug }}" {{ $divisionSlug === $division->slug ? 'selected' : '' }}>
                                    {{ $division->name }}{{ !$division->active ? ' (Legacy)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="award-filter-group">
                        <span class="filter-label">Sort</span>
                        <select id="rarity-sort" class="form-control" style="width: auto;">
                            <option value="default">Default</option>
                            <option value="rarity-desc">Rarity (Highest First)</option>
                            <option value="rarity-asc">Rarity (Lowest First)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        @php
            $clanTieredGroups = collect($tieredGroups)->whereNull('division_id');
        @endphp

        @if (($clanAwards->isNotEmpty() || $clanTieredGroups->isNotEmpty()) && !$divisionSlug)
            <div class="award-section">
                <div class="award-section-header">
                    <i class="fa fa-globe award-section-icon"></i>
                    <span class="award-section-name">Clan-Wide Awards</span>
                    <span class="award-section-count">{{ $clanAwards->count() + $clanTieredGroups->count() }}</span>
                </div>
                <div class="row award-grid">
                    @foreach($clanTieredGroups as $group)
                        @include('division.awards.partials.tiered-card', ['group' => $group])
                    @endforeach
                    @foreach ($clanAwards as $award)
                        @include('division.awards.partials.award-card', ['award' => $award])
                    @endforeach
                </div>
            </div>
        @endif

        @forelse ($activeAwards as $divisionName => $awards)
            @php
                $division = $awards->first()->division;
                $divisionTieredGroups = collect($tieredGroups)->where('division_id', $division->id);
            @endphp
            <div class="award-section">
                <div class="award-section-header">
                    <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" class="award-section-logo">
                    <span class="award-section-name">{{ $divisionName }}</span>
                    <span class="award-section-count">{{ $awards->count() + $divisionTieredGroups->count() }}</span>
                </div>
                <div class="row award-grid">
                    @foreach($divisionTieredGroups as $group)
                        @include('division.awards.partials.tiered-card', ['group' => $group])
                    @endforeach
                    @foreach ($awards as $award)
                        @include('division.awards.partials.award-card', ['award' => $award])
                    @endforeach
                </div>
            </div>
        @empty
            @if ($clanAwards->isEmpty() && $legacyAwards->isEmpty())
                <div class="text-center text-muted">
                    <p>There are currently no available awards.</p>
                </div>
            @endif
        @endforelse

        @if ($legacyAwards->isNotEmpty() && !$divisionSlug)
            <div class="award-section award-section-legacy">
                <div class="award-section-header">
                    <i class="fa fa-archive award-section-icon"></i>
                    <span class="award-section-name">Legacy Awards</span>
                    <span class="award-section-count">{{ $legacyAwards->flatten()->count() }}</span>
                </div>
                <p class="text-muted m-b-md" style="font-size: 12px;">Awards from divisions that are no longer active. No longer requestable but remain on member profiles.</p>
                @foreach ($legacyAwards as $divisionName => $awards)
                    @php $division = $awards->first()->division; @endphp
                    <div class="award-section-subheader">
                        <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" style="width: 14px; height: 14px; opacity: 0.5; vertical-align: middle; margin-right: 6px;">
                        <span>{{ $divisionName }}</span>
                    </div>
                    <div class="row award-grid">
                        @foreach ($awards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award, 'legacy' => true, 'small' => true])
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif

    </div>
@endsection
