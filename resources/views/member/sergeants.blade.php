@extends('application.base')

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

        @foreach ($divisions as $division)

            <div class="row m-b-xl">
                <div class="col-md-12">
                    <h4>
                        <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-medium" />
                        {{ $division->name }}
                        <span class="badge">{{ $division->sergeants_count }}</span>
                        <small class="pull-right text-muted">{{ count($division->members) }} Members</small>
                    </h4>
                    <hr />

                    @foreach ( $division->sergeants as $member)

                        <a href="{{ route('member', $member->clan_id) }}"
                           class="col-lg-3 panel panel-filled m-r m-b"
                           style="border-left-color: {{ $member->rank->color }}; border-left-width: 2px;">
                            <span class="panel-body">
                                    {!! $member->present()->rankName !!}
                                <br />
                                <span class="slight text-muted text-uppercase">
                                    {{ $member->position->name }}
                                </span>
                            </span>
                        </a>


                    @endforeach

                </div>
            </div>
        @endforeach

    </div>
@stop