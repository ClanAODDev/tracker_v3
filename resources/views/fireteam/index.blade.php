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
                            <a href="{{ route('fireteams.show', $fireteam->id) }}" class="pull-left m-r-sm">
                                <i class="fa fa-search"></i></a>
                            <div class="panel-toggle">
                                {{ $fireteam->name }}
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
                            <span style="color: #41eacf">&#x2726; {{ $fireteam->owner_light }}</span>
                        </a>

                        @foreach ($fireteam->players as $member)
                            <a class="badge" href="{{ route('member', $member->getUrlParams()) }}">
                                <i class="fa fa-circle text-muted"></i> {{ $member->name }}
                                <span style="color: #41eacf">&#x2726; {{ $member->pivot->light }}</span>
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

    <div class="modal fade in" id="create-fireteam" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <form action="{{ route('fireteams.store') }}" method="post" id="create-fireteam-form">
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="modal-title text-uppercase">Create Fireteam</h4>
                        <div class="form-group">
                            <label for="name">Title of fireteam</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="players_needed">Number of Players Needed</label>
                            {{ Form::selectRange('players_needed', 1, 5, 1, ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group m-t-md row">
                            <div class="col-xs-6">
                                <label for="type">
                                    Type of fireteam
                                </label>
                                <select name="type" id="type" class="form-control">
                                    <option value="raid">Raid</option>
                                    <option value="crucible">Crucible</option>
                                    <option value="strikes">Strikes</option>
                                    <option value="nightfall">Nightfall</option>
                                    <option value="trials of the nine">Trials of the Nine</option>
                                    <option value="down for anything">Down for anything</option>
                                </select>
                            </div>
                            <div class="col-xs-6">
                                <label for="light">
                                    <span style="color: #41eacf">✦</span> Your light level
                                </label>
                                <input type="number" class="form-control" name="light" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Details</label>
                            <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="m-t-md">
                            <small>By creating this fireteam, you agree to coordinate and communicate with fellow fireteam members. You will be notified when your fireteam is full.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Create Fireteam</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade in" id="join-fireteam" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <form action="#" method="post" id="join-fireteam-form">
                {{ method_field('PUT') }}
                {{ csrf_field() }}
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="modal-title text-uppercase">Join Fireteam</h4>
                        <div class="form-group m-t-md">
                            <label for="light">
                                <span style="color: #41eacf">✦</span> Your current light level
                            </label>
                            <input type="number" class="form-control" name="light" required />
                        </div>

                        <div class="form-group">
                            <p>By joining this fireteam, you agree to participate at the time the event is scheduled for. You will receive an email notification when the fireteam leader confirms the fireteam.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Join Fireteam</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
      function updateFireteamForm (fireteamId) {
        let route = '{{ route('fireteams.index') }}/' + fireteamId;
        $('#join-fireteam-form').attr('action', route);
      }
    </script>
@stop