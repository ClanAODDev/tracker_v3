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
            @component('application.components.link-block')
                @slot('link'){{ route('adminCreateDivision') }}@endslot
                @slot('color') panel-c-accent @endslot
                @slot('data') Create New Division @endslot
            @endcomponent
            @component('application.components.link-block')
                @slot('link'){{ route('adminCreateHandle') }}@endslot
                @slot('color') panel-c-accent @endslot
                @slot('data') Create New Handle @endslot
            @endcomponent
        </div>

        <h4 class="m-t-xl">Manage Tracker</h4>
        <hr />
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
                        <a href="#handles" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-user fa-lg text-muted"></i> <span class="hidden-xs">Handles</span>
                        </a>
                    </li>

                    <li>
                        <a href="#crons" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-cog fa-lg text-muted"></i> <span class="hidden-xs">Activity Log</span>
                        </a>
                    </li>

                    <li>
                        <a href="#tags" data-toggle="tab" aria-expanded="false">
                            <i class="fa fa-tags fa-lg text-muted"></i> <span class="hidden-xs">Tags</span>
                        </a>
                    </li>
                </ul>
                {{-- end profile edit nav --}}
                <div class="margin-top-20">

                    <div id="settings-form" class="tab-content">

                        <div class="tab-pane active" id="users">
                            <div class="panel-body">
                                @include('admin.partials.users-list')
                            </div>
                        </div>

                        <div class="tab-pane" id="divisions">
                            <div class="panel-body">
                                @include('admin.partials.division-status-list')
                            </div>
                        </div>

                        <div class="tab-pane" id="handles">
                            <div class="panel-body">
                                @include('admin.partials.handles-list')
                            </div>
                        </div>

                        <div class="tab-pane" id="crons">
                            <div class="panel-body">
                                @include('admin.partials.activity-log')
                            </div>
                        </div>

                        <div class="tab-pane" id="tags">
                            <div class="panel-body">
                                @include('admin.forms.tags')
                                @include('admin.partials.tag-table')
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

@stop