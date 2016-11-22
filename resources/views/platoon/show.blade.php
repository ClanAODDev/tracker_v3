@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

    <div class="row">
        <div class="col-xs-12">
            <h2>
                @include('division.partials.icon')

                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>

    <ul class="nav nav-tabs margin-top-20">
        <li class="active">
            <a href="#platoon-view" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-cube fa-lg"></i><span class="hidden-sm hidden-xs"> Platoon View</span>
            </a>
        </li>

        {{-- Only show squads tab if squads are found --}}
        @if (count($platoon->squads))
            <li>
                <a href="#squad-view" data-toggle="tab" aria-expanded="false">
                    <i class="fa fa-cubes fa-lg"></i><span class="hidden-sm hidden-xs"> Squads</span>
                </a>
            </li>
        @endif
    </ul>

    <div class="row margin-top-20">
        <div class="col-md-8">
            <div id="platoon" class="tab-content">

                <div class="tab-pane fade active in" id="platoon-view">
                    @include('platoon.partials.platoon_members')
                </div>

                <div class="tab-pane fade in" id="squad-view">
                    <div class="row">
                        @if (count($platoon->squads))
                            @include('platoon.partials.squads')
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @include('platoon.partials.member_stats')
        </div>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js') !!}"></script>
@stop
