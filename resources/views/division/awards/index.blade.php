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
            </div>
        </div>

        @if ($clanAwards->isNotEmpty() && !$divisionSlug)
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <i class="fa fa-globe"></i> Clan-Wide Awards
                    <span class="badge pull-right">{{ $clanAwards->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach ($clanAwards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @forelse ($divisionAwards as $divisionName => $awards)
            @php $division = $awards->first()->division; @endphp
            <div class="panel panel-filled">
                <div class="panel-heading">
                    <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;">
                    {{ $divisionName }}
                    <span class="badge pull-right">{{ $awards->count() }}</span>
                </div>
                <div class="panel-body">
                    <div class="row">
                        @foreach ($awards as $award)
                            @include('division.awards.partials.award-card', ['award' => $award])
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            @if ($clanAwards->isEmpty())
                <div class="text-center text-muted">
                    <p>There are currently no available awards.</p>
                </div>
            @endif
        @endforelse

        <div class="panel-footer text-muted">
            <span class="text-mythic">&#9632;</span> Mythic (0)
            <span class="text-legendary" style="margin-left: 10px;">&#9632;</span> Legendary (1-5)
            <span class="text-epic" style="margin-left: 10px;">&#9632;</span> Epic (6-20)
            <span class="text-rare" style="margin-left: 10px;">&#9632;</span> Rare (21-50)
            <span class="text-common" style="margin-left: 10px;">&#9632;</span> Common (50+)
        </div>
    </div>
@endsection
