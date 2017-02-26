@extends('application.base')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="division-header">
                    <div class="header-icon">
                        <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs text-uppercase">Manage Division</h3>
                        <small>
                            {{ $division->name }} Division
                        </small>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        {{-- Edit profile nav --}}
        <div class="tabs-container">

            <ul class="nav nav-tabs">
                <li class="divisions active">
                    <a data-toggle="tab" href="#general-settings">
                        <i class="fa fa-sliders fa-lg"></i> <span class="hidden-xs">General</span>
                    </a>
                </li>
                <li class="leaders">
                    <a data-toggle="tab" href="#leader-settings">
                        <i class="fa fa-shield fa-lg"></i> <span class="hidden-xs">Leadership</span>
                    </a>
                </li>
                <li>
                    <a href="#recruiting-settings" data-toggle="tab">
                        <i class="fa fa-user-plus fa-lg"></i> <span class="hidden-xs">Recruiting</span>
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#locality-settings">
                        <i class="fa fa-language fa-lg"></i> <span class="hidden-xs">Locality</span>
                    </a>
                </li>
                <li>
                    <a data-toggle="tab" href="#slack-settings">
                        <i class="fa fa-slack fa-lg"></i> <span class="hidden-xs">Slack</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="general-settings" class="tab-pane divisions active">
                    <div class="panel-body">
                        @include('division.forms.general-settings')
                    </div>
                </div>

                <div id="leader-settings" class="tab-pane">
                    <div class="panel-body">
                        @include('division.forms.leadership')
                    </div>
                </div>

                <div class="tab-pane fade in" id="recruiting-settings">
                    <div class="panel-body">
                        @include('division.forms.recruiting')
                    </div>
                </div>

                <div id="locality-settings" class="tab-pane">
                    <div class="panel-body">
                        @include('division.forms.locality')
                    </div>
                </div>

                <div id="slack-settings" class="tab-pane">
                    <div class="panel-body">
                        @include('division.forms.slack')
                    </div>
                </div>
            </div>
        </div>
@stop