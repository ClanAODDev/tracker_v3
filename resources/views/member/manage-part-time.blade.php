@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position?->getLabel() ?? "No Position" }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('application.partials.back-breadcrumb', ['page' => 'profile', 'link' => route('member', $member->getUrlParams())])

        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Available Divisions</h4>
                        <hr />
                        <div class="row">
                            @foreach ($divisions as $division)
                                <div class="col-md-6">
                                    <a href="{{ route('assignPartTimer', [$division->slug, $member->clan_id]) }}"
                                       class="panel panel-filled panel-c-danger">
                                        <div class="panel-body m-b-none">
                        <span class="text-uppercase">
                            {{ $division->name }}
                        </span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Part-time Divisions</h4>
                        <hr />
                        <div class="row">
                            @foreach ($member->partTimeDivisions as $division)
                                <div class="col-md-6">
                                    <a href="{{ route('removePartTimer', [$division->slug, $member->clan_id]) }}"
                                       class="panel panel-filled panel-c-success">
                                        <div class="panel-body m-b-none">
                        <span class="text-uppercase">
                            {{ $division->name }}
                        </span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection