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
        <a href="{{ route('fireteams.index') }}" class="btn btn-default"> <i
                    class="fa fa-arrow-left"></i> Back to Fireteams</a>

        @if ($fireteam->players_needed == $fireteam->players_count && !$fireteam->confirmed)
            <div class="alert alert-warning m-t-md">
                <strong>Fireteam is full.</strong> Waiting for fireteam leader to confirm...
            </div>
        @endif

        @if($fireteam->confirmed)
            <div class="alert alert-success m-t-md">
                <strong>Fireteam is confirmed.</strong> All party members should convene on Teamspeak at the desginated date and time.
            </div>
        @endif

        <h2 class="text-uppercase m-t-lg">
            @if ($fireteam->confirmed)
                <i class="fa fa-check text-success"></i>
            @endif
            {{ $fireteam->name }}
            <a href="{{ route('fireteams.byType', $fireteam->type) }}"
               class="badge text-uppercase">{{ $fireteam->type }}</a>
        </h2>

        <div class="fireteams m-t-lg">

            <h4>
                Fireteam Members
                @if ($fireteam->players()->count() > 0)
                    <a class="badge" target="_blank" title="PM Fireteam"
                       href="{{ doForumFunction($fireteam->players->pluck('clan_id')->toArray(), 'pm') }}">
                        <i class="fa fa-comment"></i>
                    </a>
                @endif
                <div class="pull-right">
                    @if ($fireteam->slotsAvailable > 0)
                        <div class="label {{ $fireteam->spotsColor }} text-uppercase">
                            {{ $fireteam->slotsAvailable }} {{ str_plural('slot', $fireteam->slotsAvailable) }} Left
                        </div>
                    @else
                        <div class="label label-default text-muted">No Slots Available</div>
                    @endif
                </div>
            </h4>
            <table class="table table-hover">
                <tr>
                    <td>
                        <a class="badge" href="{{ route('member', $fireteam->owner->getUrlParams()) }}">
                            <i class="fa fa-circle text-muted"></i> {{ $fireteam->owner->name }}
                            <span style="color: #41eacf">&#x2727; {{ $fireteam->owner_light }}</span>
                        </a>
                    </td>
                </tr>

                @foreach ($fireteam->players as $member)
                    <tr>
                        <td>
                            <a class="badge" href="{{ route('member', $member->getUrlParams()) }}">
                                <i class="fa fa-circle text-muted"></i>
                                {{ $member->name }}
                                <span style="color: #41eacf">&#x2727; {{ $member->pivot->light }}</span>
                            </a>
                        </td>
                    </tr>
                @endforeach

                @if ($fireteam->slotsAvailable)
                    @for ($i = 1; $i <= $fireteam->slotsAvailable; $i++)
                        <tr>
                            <td>
                                <a class="badge" href="#" data-toggle="modal" data-target="#join-fireteam"
                                   onclick="updateFireteamForm({{ $fireteam->id }});">
                                    <i class="fa fa-circle-o text-success"></i> Spot Open</a>
                            </td>
                        </tr>
                    @endfor
                @endif
            </table>
        </div>

        @if ($fireteam->description)
            <div class="panel panel-filled m-t-xl">
                <div class="panel-heading">Fireteam Details</div>
                <div class="panel-body">
                    <p>{{ $fireteam->description }}</p>
                </div>
            </div>
        @endif

        @if (!$fireteam->confirmed)
            <div class="row m-t-md">
                <div class="col-md-12">
                    @if ($fireteam->players->contains(auth()->user()->member_id))
                        <a href="{{ route('fireteams.leave', $fireteam->id) }}"
                           class="btn btn-warning m-t-lg">Leave Fireteam</a>
                    @endif

                    @if (auth()->user()->member_id === $fireteam->owner_id)
                        <div class="row">
                            <div class="col-xs-6">
                                <form action="{{ route('fireteams.destroy', $fireteam->id) }}" method="post">
                                    {{ method_field('delete') }}
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to cancel this fireteam?');">Cancel Fireteam
                                    </button>
                                </form>
                            </div>
                            <div class="col-xs-6">
                                @if ($fireteam->players_needed == $fireteam->players_count)
                                    <form action="{{ route('fireteams.confirm', $fireteam->id) }}" method="post">
                                        {{ csrf_field() }}
                                        <button type="submit" class="btn btn-success pull-right"
                                                onclick="return confirm('Are you sure you want to confirm and close this fireteam?');">Confirm Fireteam
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                    @endif
                </div>
            </div>
        @endif

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
                                    <span style="color: #41eacf">&#x2727;</span> Your light level
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
                                <span style="color: #41eacf">&#x2727;</span> Your current light level
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
        let route = '{{ route('fireteams.index') }}/' + fireteamId;
        $('#join-fireteam-form').attr('action', route);
      }
    </script>
@stop