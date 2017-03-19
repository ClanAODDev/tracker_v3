@extends('application.base')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Administration CP
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-xs-12">

                {{-- Edit profile nav --}}
                <ul class="nav nav-tabs margin-top-20">
                    <li class="active">
                        <a href="#users" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-users fa-lg text-muted"></i> <span class="hidden-xs">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="#divisions" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-toggle-on fa-lg text-muted"></i> <span class="hidden-xs">Divisions</span>
                        </a>
                    </li>
                    <li>
                        <a href="#aliases" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-user-circle-o fa-lg text-muted"></i> <span class="hidden-xs">Aliases</span>
                        </a>
                    </li>
                    <li>
                        <a href="#crons" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-wrench fa-lg text-muted"></i> <span class="hidden-xs">Cron Jobs</span>
                        </a>
                    </li>
                </ul>
                {{-- end profile edit nav --}}
                <div class="margin-top-20">

                    <div id="settings-form" class="tab-content">

                        <div class="tab-pane active" id="users">
                            <div class="panel-body">
                               @include('admin.partials.user-table')
                            </div>
                        </div>

                        <div class="tab-pane" id="divisions">
                            <div class="panel-body">
                                @include('admin.forms.manage-divisions-form')
                            </div>
                        </div>

                        <div class="tab-pane" id="aliases">
                            <div class="panel-body">
                                @include('admin.forms.manage-aliases-form')
                            </div>
                        </div>

                        <div class="tab-pane" id="crons">
                            <div class="panel-body">
                                Manage cron jobs for application
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

@stop