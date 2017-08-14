@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            <a href="{{ route('division', $division->abbreviation) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            </a>
        @endslot
        @slot ('heading')
            {{ $platoon->name }}
            @include('platoon.partials.edit-platoon-button', ['division' => $division])
        @endslot
        @slot ('subheading')
            {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        @include('platoon.partials.alerts')

        <div class="row">
            <div class="col-md-9">

                <ul class="nav nav-tabs">
                    <li role="presentation">
                        <a href="{{ route('platoon', [$division->abbreviation, $platoon]) }}">
                            <i class="fa fa-cube fa-lg"></i> {{ $division->locality('Platoon') }}
                        </a>
                    </li>
                    <li role="presentation" class="active">
                        <a href="#"><i class="fa fa-cubes fa-lg"></i> {{ str_plural($division->locality('Squad')) }}</a>
                    </li>
                    @can('create', [App\Squad::class, $division])
                        <li role="presentation" class="pull-right slight">
                            <a href="{{ route('createSquad', [$division->abbreviation, $platoon]) }}">
                                <i class="fa fa-plus text-success"></i> New {{ $division->locality('squad') }}</a>
                        </li>
                    @endcan
                </ul>

                <div class="panel panel-filled">
                    @include('platoon.partials.squads')
                </div>

            </div>

            <div class="col-md-3">
                @include('platoon.partials.member_stats')
            </div>
        </div>

    </div>
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2.0') !!}"></script>
@stop
