@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('divisions', $division) !!}

    <div class="page-header">
        <h2>
            <img src="/images/game_icons/48x48/{{ $division->abbreviation }}.png"/>
            <strong>{{ $division->name }} Division</strong>

            {{-- if user is division leader --}}
            @if (Auth::user()->role->id >= 3 || Auth::user()->developer)
                <div class="btn-group pull-right">
                    <a class="btn btn-default edit-div disabled" href="#" target="_blank"><i class="fa fa-pencil"></i>
                        <span class="hidden-xs hidden-sm">Edit Division</span></a>
                </div>
            @endif

        </h2>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-primary">

                {{-- locality --}}
                <div class="panel-heading">Platoons</div>
                <div class="list-group">
                    @if (count($division->platoons))
                        @foreach ($division->platoons as $platoon)
                            <a href="{{ action('PlatoonController@show', [$platoon->id]) }}"
                               class=" list-group-item">
                                <h5 class="pull-right text-muted big-num count-animated">{{ $platoon->members->count() }}</h5>
                                <h4 class="list-group-item-heading"><strong>{{ $platoon->name }}</strong></h4>
                                <p class="list-group-item-text text-muted">Leader</p>
                            </a>
                        @endforeach
                    @else
                        <li class="list-group-item text-muted">No platoons currently exist for this division.</li>
                    @endif

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info">
                <div class="panel-heading">Division Command Staff</div>
                @if (count($division->leaders))
                    @foreach ($division->leaders as $leader)
                        <a href="members/{{ $leader->clan_id }}" class="list-group-item">
                            <h5 class="pull-right"><i class="fa fa-shield fa-2x text-muted"></i></h5>
                            <h4 class="list-group-item-heading">
                                <strong>{{ $leader->rank->abbreviation }} {{ $leader->name }}</strong></h4>
                            <p class="list-group-item-text text-muted"><?php echo $leader->position->name; ?></p>
                        </a>
                    @endforeach
                @else
                    <li class="list-group-item text-muted">This division has no assigned leadership</li>
                @endif
            </div>
        </div>
    </div>

@endsection
