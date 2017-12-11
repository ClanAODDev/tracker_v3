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
            Find a Destiny 2 Fireteam
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include('application.partials.errors')

        <h2>FIND A FIRETEAM</h2>
        <p>Search existing fireteams and join a slot, or create your own</p>

        <div class="fireteams m-t-lg">
            @forelse ($fireteams as $fireteam)
                <div class="panel panel-filled collapsed">
                    <div class="panel-heading">
                        <h4 class="text-uppercase">
                            <a href="{{ route('fireteams.byType', $fireteam->type) }}"
                               class="badge text-uppercase pull-right">{{ $fireteam->type }}</a>
                            <a href="{{ route('fireteams.show', $fireteam->id) }}"
                               class="pull-left m-r-sm" style="color: white; z-index:9999">
                                {{ $fireteam->name }}
                            </a>
                            <div class="panel-toggle">

                                @if ($fireteam->slotsAvailable > 0)
                                    <small class="label {{ $fireteam->spotsColor }} text-uppercase">
                                        {{ $fireteam->slotsAvailable }} {{ str_plural('slot', $fireteam->slotsAvailable) }} Left
                                    </small>
                                @else
                                    <small class="label label-default text-muted">No Slots</small>
                                @endif
                            </div>
                        </h4>
                    </div>

                    <div class="panel-body">
                        <a class="badge" href="{{ route('member', $fireteam->owner->getUrlParams()) }}">
                            <i class="fa fa-circle text-muted"></i> {{ $fireteam->owner->name }}
                            <span style="color: #41eacf">&#x2727; {{ $fireteam->owner_light }}</span>
                        </a>

                        @foreach ($fireteam->players as $member)
                            <a class="badge" href="{{ route('member', $member->getUrlParams()) }}">
                                <i class="fa fa-circle text-muted"></i> {{ $member->name }}
                                <span style="color: #41eacf">&#x2727; {{ $member->pivot->light }}</span>
                            </a>
                        @endforeach

                        @if ($fireteam->slotsAvailable)
                            @for ($i = 1; $i <= $fireteam->slotsAvailable; $i++)
                                <a href="#" data-toggle="modal" data-target="#join-fireteam"
                                   class="btn btn-success btn-xs"
                                   onclick="updateFireteamForm({{ $fireteam->id }});">
                                    <i class="fa fa-circle-o text-success"></i> Slot Open</a>
                            @endfor
                        @endif

                        @if ($fireteam->description)
                            <div class="bs-example m-t-md">
                                <p>{{ $fireteam->description }}</p>
                            </div>
                        @endif

                        @auth
                        @if ($fireteam->players->contains(auth()->user()->member_id))
                            <a href="{{ route('fireteams.leave', $fireteam->id) }}"
                               class="btn btn-warning btn-xs pull-right">Leave Fireteam</a>
                        @endif
                        @endauth

                    </div>
                </div>
            @empty
                <h4 class="text-uppercase">No fireteams found</h4>
                <p>Either there are no fireteams, or none match your search criteria. <a
                            href="{{ route('fireteams.index') }}">Reset search</a>?</p>
            @endforelse
        </div>

        <hr>

        <a href="{{ route('fireteams.index') }}" class="btn btn-default">Show all Fireteams</a>
        <a href="#" data-toggle="modal" data-target="#create-fireteam" class="btn btn-success">Create Fireteam</a>

    </div>

    @include('fireteam.modals')

    <script>
      function updateFireteamForm (fireteamId) {
        let route = '{{ route('fireteams.index') }}/' + fireteamId;
        $('#join-fireteam-form').attr('action', route);
      }
    </script>
@stop