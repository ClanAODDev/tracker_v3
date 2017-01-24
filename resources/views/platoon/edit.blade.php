@extends('layouts.app')
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


    <hr/>

    @can('create', [\App\Squad::class, $division])
        <li class="pull-right">
            <a href="{{ route('createSquad', [$division->abbreviation, $platoon]) }}"><i
                        class="fa fa-plus fa-lg"></i><span
                        class="hidden-xs hidden-sm">Create {{ $division->locality('squad') }}</span>
            </a>
        </li>
    @endcan

@stop