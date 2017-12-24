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

    <div class="container-fluid" id="container">
        @include('application.partials.errors')

        <h2>FIND A FIRETEAM</h2>
        <p>Search existing fireteams and join a slot, or create your own</p>

        <div class="row">
            <div class="col-md-12">
                <div v-if="fireteams.length > 0">
                    <div class="panel panel-filled" v-for="fireteam in fireteams">
                        <div class="panel-heading">
                            <h4 class="text-uppercase">
                                <a href="#"
                                   class="badge text-uppercase pull-right">@{{ fireteam.type }}</a>
                                <a href="#"
                                   class="pull-left m-r-sm" style="color: white; z-index:9999">
                                    @{{ fireteam.name }}
                                </a>

                                <small v-if="fireteam.slots_available > 0" class="label text-uppercase">
                                    @{{ fireteam.slots_available }} Slots left
                                </small>

                                <small v-else class="label label-default text-muted">No Slots</small>

                            </h4>
                        </div>

                        <div class="panel-body">
                            <a class="badge" href="#">
                                <i class="fa fa-circle text-muted"></i> @{{ fireteam.owner_bnet }}
                                <span style="color: #41eacf">&#x2727; @{{ fireteam.owner_light }}</span>
                            </a>

                            <span v-if="fireteam.players">
                            <a class="badge" href="#" v-for="player in fireteam.players">
                                <i class="fa fa-circle text-muted"></i> @{{ player.bnet }}
                                <span style="color: #41eacf">&#x2727; @{{ player.light }}</span>
                            </a>
                        </span>

                            <button v-for="i in fireteam.slots_available"
                               @click="joinFireteam(fireteam, {'bnet': 'guybrush#1852', 'light': 400})"
                               class="btn btn-success btn-xs">
                                <i class="fa fa-circle-o text-success"></i> Slot Open</button>

                            {{--@if ($fireteam->slotsAvailable)
                                @for ($i = 1; $i <= $fireteam->slotsAvailable; $i++)
                                    <a href="#" data-toggle="modal" data-target="#join-fireteam"
                                       class="btn btn-success btn-xs"
                                       onclick="updateFireteamForm({{ $fireteam->id }});">
                                        <i class="fa fa-circle-o text-success"></i> Slot Open</a>
                                @endfor
                            @endif
    --}}
                            <div class="bs-example m-t-md" v-if="fireteam.description">
                                <p>@{{ fireteam.description }}</p>
                            </div>

                            {{-- @auth
                             @if ($fireteam->players->contains(auth()->user()->member_id))
                                 <a href="{{ route('fireteams.leave', $fireteam->id) }}"
                                    class="btn btn-warning btn-xs pull-right">Leave Fireteam</a>
                             @endif
                             @endauth--}}

                        </div>
                    </div>
                </div>
                <div v-else>
                    <h4 class="text-uppercase text-warning">No fireteams</h4>
                    <p>There don't seem to be any fireteams. Perhaps you should make one?</p>
                </div>
            </div>
            <div class="col-md-12">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="modal-title text-uppercase">Create Fireteam</h4>
                        <div class="row m-t-md">
                            <div class="col-xs-8">
                                <div class="form-group">
                                    <label for="name">Title of fireteam</label>
                                    <input type="text" class="form-control" name="name" required
                                           v-model.trim="newFireteam.name">
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="form-group">
                                    <label for="players_needed">Players Needed</label>
                                    <select v-model="newFireteam.players_needed" class="form-control">
                                        <option :value="i" v-for="i in 5">@{{ i }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-xs-4">
                                <label for="type">
                                    Type of fireteam
                                </label>
                                <select name="type" id="type" class="form-control" v-model="newFireteam.type">
                                    <option value="raid">Raid</option>
                                    <option value="crucible">Crucible</option>
                                    <option value="strikes">Strikes</option>
                                    <option value="trials of the nine">Trials of the Nine</option>
                                    <option value="down for anything">Down for anything</option>
                                </select>
                            </div>
                            <div class="col-xs-4">
                                <label for="light">
                                    <span style="color: #41eacf">&#x2727;</span> Your light level
                                </label>
                                <input type="number" v-model="newFireteam.owner_light"
                                       class="form-control" name="light" required />
                            </div>
                            <div class="col-xs-4">
                                <label for="bnet">Battlenet</label>
                                <input type="text" class="form-control" v-model="newFireteam.owner_bnet">
                            </div>
                        </div>

                        <div class="form-group m-t-md">
                            <label for="description">Details</label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                      v-model="newFireteam.description"></textarea>
                        </div>

                        <div class="m-t-md">
                            <small>By creating this fireteam, you agree to coordinate and communicate with fellow fireteam members. You will be notified when your fireteam is full.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent" @click="addFireteam">Create Fireteam</button>
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('fireteams.index') }}" class="btn btn-default">Show all Fireteams</a>
        <a href="#" data-toggle="modal" data-target="#create-fireteam" class="btn btn-success">Create Fireteam</a>

    </div>

    @include('fireteam.modals')
@stop

@section('footer_scripts')
    <script src="{!! asset('/js/fireteams.js?v2') !!}"></script>
@stop