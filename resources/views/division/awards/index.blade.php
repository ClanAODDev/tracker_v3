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
                        <div class="text-muted">Requestable</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-b-lg">
            <div class="col-md-12">
                <p>Stand out as a member of the community by earning one or more of the awards listed below.</p>
                <div class="rarity-legend">
                    @foreach (config('aod.awards.rarity') as $key => $rarity)
                        <div class="rarity-legend-item">
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
        </div>

        @if ($clanAwards->isNotEmpty() && !$divisionSlug)
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <i class="fa fa-globe"></i> Clan-Wide Awards
                    <span class="badge pull-right">{{ $clanAwards->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row award-grid">
                        @foreach ($clanAwards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @forelse ($activeAwards as $divisionName => $awards)
            @php $division = $awards->first()->division; @endphp
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;">
                    {{ $divisionName }}
                    <span class="badge pull-right">{{ $awards->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row award-grid">
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
