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

        <h4>Search Members</h4>
        <form action="{{ route('memberSearch') }}">
            <div class="form-group" style="position: relative">
                <input type="text" class="form-control" name="name" value="{{ request()->name }}" />
                <i class="fa fa-search pull-right" style="position: absolute; right: 10px; top: 10px;"></i>
            </div>
        </form>

        @if (request()->name)
            <hr />
            @forelse($members as $member)
                <a class="panel" href="{{ route('member', $member->getUrlParams()) }}"
                   style="padding-left: 30px; margin-bottom: 0;">
                    <div class="panel-body">
                        <h4 class="m-b-none">
                            <span class="slight">{{ $loop->iteration }}. </span>
                            {!! str_limit($member->present()->rankName, 15) !!}
                            <small class="slight text-muted">[{{ $member->clan_id }}]</small>
                            <small class="pull-right">{{ $member->division->name ?? "Ex-AOD" }}</small>
                        </h4>
                    </div>
                </a>
            @empty
                <div class="panel text-muted">
                    <div class="panel-body" style="padding-top: 55px; pointer-events: none;">
                        <h4 class="text-muted"><i class="fa fa-times-circle"></i> No results using your search criteria
                        </h4>
                    </div>
                </div>
            @endforelse
        @endif

    </div>

@stop