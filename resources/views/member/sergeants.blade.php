@extends('application.base')

@section('content')


@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h3>Sergeants+</h3>
        <hr />

        @foreach ($divisions as $division)
            <div class="panel panel-filled m-b-xl">
                <div class="panel-heading" id="{{ $division->abbreviation }}">
                    <h4>{{ $division->name }} ({{ $division->sergeants_count }})</h4>
                    <hr class="m-b-none" />
                </div>
                <div class="panel-body">

                    @foreach ( $division->sergeants as $member)

                        <a href="{{ route('member', $member->clan_id) }}"
                           class="col-lg-3 panel panel-filled m-r">
                            <div class="panel-body">
                                {!! $member->present()->nameWithIcon(true) !!}
                            </div>

                        </a>
                    @endforeach

                </div>
            </div>
        @endforeach
    </div>

@stop