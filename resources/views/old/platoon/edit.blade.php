@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

    <div class="row">
        <div class="col-sm-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $platoon->name }}</strong>
                <small>Manage Platoon</small>
            </h2>
        </div>
    </div>
    <hr />

    @foreach ($squads as $squad)

    @endforeach

    @can('create', [\App\Squad::class, $division])
        <a href="{{ route('createSquad', [$division->abbreviation, $platoon]) }}"
           class="btn btn-default"><i class="fa fa-plus fa-lg"></i>
            <span class="hidden-xs hidden-sm">Create {{ $division->locality('squad') }}</span>
        </a>
    @endcan

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/manage-squads.js') !!}"></script>
@stop