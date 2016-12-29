@extends('layouts.app')
@section('content')

    <div class="row">
        <div class="col-xs-12">
            <h2>
                <strong>Tracker</strong>
                <small>Administration</small>
            </h2>
            <hr/>

            {{-- Edit profile nav --}}
            <ul class="nav nav-tabs margin-top-20">
                <li class="active">
                    <a href="#stats" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-line-chart fa-lg text-muted"></i><span class="hidden-xs">Statistics</span>
                    </a>
                </li>
                <li>
                    <a href="#users" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-users fa-lg text-muted"></i><span class="hidden-xs">Users</span>
                    </a>
                </li>
                <li>
                    <a href="#divisions" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-toggle-on fa-lg text-muted"></i><span class="hidden-xs">Divisions</span>
                    </a>
                </li>
                <li>
                    <a href="#aliases" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-user-circle-o fa-lg text-muted"></i><span class="hidden-xs">Aliases</span>
                    </a>
                </li>
                <li>
                    <a href="#crons" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-wrench fa-lg text-muted"></i><span class="hidden-xs">Cron Jobs</span>
                    </a>
                </li>
                <li>
                    <a href="#api" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-bolt fa-lg text-muted"></i><span class="hidden-xs">API</span>
                    </a>
                </li>
            </ul>
            {{-- end profile edit nav --}}
            <div class="margin-top-20">

                <div id="settings-form" class="tab-content">

                    <div class="tab-pane fade active in" id="stats">
                        @include('admin.partials.rankDemographic')
                    </div>

                    <div class="tab-pane fade in" id="users">
                        Manage users
                    </div>

                    <div class="tab-pane fade in" id="divisions">
                        @include('admin.forms.manageDivisionsForm')
                    </div>

                    <div class="tab-pane fade in" id="aliases">
                        Manage available aliases
                    </div>

                    <div class="tab-pane fade in" id="crons">
                        Manage cron jobs for application
                    </div>

                    <div class="tab-pane fade in" id="api">
                        <div id="passport">
                            <passport-clients ></passport-clients>
                            <passport-authorized-clients ></passport-authorized-clients>
                            <passport-personal-access-tokens ></passport-personal-access-tokens>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop