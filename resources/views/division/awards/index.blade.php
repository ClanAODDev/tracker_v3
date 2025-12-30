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

        <div class="row m-b-lg">
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">{{ $totals->awards }}</h1>
                        <div class="text-muted">Total Awards</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-warning">{{ number_format($totals->recipients) }}</span>
                        </h1>
                        <div class="text-muted">Awards Given</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-filled">
                    <div class="panel-body text-center">
                        <h1 style="margin: 0;">
                            <span class="text-success">{{ $totals->requestable }}</span>
                        </h1>
                        <div class="text-muted"><i class="fa fa-hand-pointer"></i> Requestable</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <div class="award-filters">

                    <div class="award-filter-group">
                        <span class="filter-label">Rarity</span>
                        <div class="rarity-legend">
                            @foreach (config('aod.awards.rarity') as $key => $rarity)
                                <div class="rarity-legend-item rarity-filter active" data-rarity="{{ $key }}">
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
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <i class="fa fa-globe"></i> Clan-Wide Awards
                    <span class="badge pull-right">{{ $clanAwards->count() + $clanTieredGroups->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row award-grid">
                        @foreach($clanTieredGroups as $group)
                            @include('division.awards.partials.tiered-card', ['group' => $group])
                        @endforeach
                        @foreach ($clanAwards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @forelse ($activeAwards as $divisionName => $awards)
            @php
                $division = $awards->first()->division;
                $divisionTieredGroups = collect($tieredGroups)->where('division_id', $division->id);
            @endphp
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;">
                    {{ $divisionName }}
                    <span class="badge pull-right">{{ $awards->count() + $divisionTieredGroups->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row award-grid">
                        @foreach($divisionTieredGroups as $group)
                            @include('division.awards.partials.tiered-card', ['group' => $group])
                        @endforeach
                        @foreach ($awards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award])
                        @endforeach
                    </div>
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
            <div class="panel panel-filled" style="opacity: 0.75;">
                <div class="panel-heading">
                    <i class="fa fa-archive text-muted"></i> Legacy Awards
                    <span class="badge pull-right">{{ $legacyAwards->flatten()->count() }}</span>
                </div>
                <div class="panel-body">
                    <p class="text-muted m-b-md">Awards from divisions that are no longer active. These awards are no longer requestable but remain on member profiles.</p>
                    @foreach ($legacyAwards as $divisionName => $awards)
                        @php $division = $awards->first()->division; @endphp
                        <h5 class="text-muted m-t-md">
                            <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: middle; opacity: 0.7;">
                            {{ $divisionName }}
                        </h5>
                        <div class="row award-grid">
                            @foreach ($awards as $award)
                                @include('division.awards.partials.award-card', ['award' => $award, 'legacy' => true, 'small' => true])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection
