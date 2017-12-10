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
                @php
                    $isOwner = $fireteam->owner_id == auth()->user()->member_id;
                    $isMember = $fireteam->players->contains(auth()->user()->member)
                @endphp
                <div class="panel panel-filled collapsed {{ $isOwner ? 'panel-c-success' : null }} {{ $isMember ? 'panel-c-info' : null }}">
                    <div class="panel-heading">
                        <h4 class="text-uppercase">
                            <a href="{{ route('fireteams.byType', $fireteam->type) }}"
                               class="badge text-uppercase pull-right">{{ $fireteam->type }}</a>
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
                            <span style="color: #41eacf">✦ {{ $fireteam->owner_light }}</span>
                        </a>

                        @foreach ($fireteam->players as $member)
                            <a class="badge" href="{{ route('member', $member->getUrlParams()) }}">
                                <i class="fa fa-circle text-muted"></i> {{ $member->name }}
                                <span style="color: #41eacf">✦ {{ $member->pivot->light }}</span>
                            </a>
                        @endforeach

                        @if ($fireteam->slotsAvailable)
                            @for ($i = 0; $i < $fireteam->slotsAvailable; $i++)
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

                        <div class="row m-t-md">
                            <div class="col-md-12">
                                @if ($fireteam->players->contains(auth()->user()->member_id))
                                    <a href="{{ route('fireteams.leave', $fireteam->id) }}"
                                       class="btn btn-warning m-t-lg">Leave Fireteam</a>
                                @endif

                                @if (auth()->user()->member_id === $fireteam->owner_id)
                                    <a class="badge btn-xs" target="_blank"
                                       href="{{ doForumFunction($fireteam->players->pluck('member_id')->toArray(), 'pm') }}">
                                        <i class="fa fa-comment"></i> PM Fireteam
                                    </a>
                                    <div class="pull-right">
                                        <form action="{{ route('fireteams.destroy', $fireteam->id) }}" method="post">
                                            {{ method_field('delete') }}
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-default"
                                                    onclick="return confirm('Are you sure you want to cancel this fireteam?');">Cancel Fireteam
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <p>No fireteams match that criteria. <a href="{{ route('fireteams.index') }}">Reset search</a>?</p>
            @endforelse
        </div>

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

                        <div class="form-group text-warning">
                            <p>By joining this fireteam, you agree to participate at the time the event is scheduled for. You will receive email notification when all slots are filled.</p>
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
        let route = '/fireteams/' + fireteamId;
        $('#join-fireteam-form').attr('action', route);
      }
    </script>
@stop