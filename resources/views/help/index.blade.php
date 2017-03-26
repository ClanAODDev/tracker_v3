@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Documentation
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Documentation and frequently asked questions
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-filled">
                    <div class="panel-body">
                        <h3>Search</h3>
                        <p>Search for information about a particular topic, or select from the frequently asked questions provided below.</p>

                        <div class="form-group">
                            <input class="form-control" placeholder="What are you looking for ?">
                        </div>

                    </div>

                </div>

            </div>
        </div>
        <div class="row">

            <div class="col-md-4">

                <div class="panel-group">

                    <ul class="list-unstyled nav-tabs">
                        <li class="panel panel-filled support-question active">
                            <a href="#why-use-tracker" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Why use the tracker?</p>
                                    <p>My division has used spreadsheets for years. Why should we use the tracker instead?</p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#user-access-matrix" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">How is user access broken down?</p>
                                    <p>What things does each role have access to?</p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#answer3" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 3</p>
                                    <p>Various have evolved over the years, sometimes by accident.</p>
                                </div>
                            </a>
                        </li>
                        <li class="panel panel-filled support-question">
                            <a href="#answer4" data-toggle="tab">
                                <div class="panel-body">
                                    <p class="font-bold c-white">Support question 4</p>
                                    <p>Versions have evolved over the years, sometimes by accident, sometimes on purpose</p>
                                </div>
                            </a>
                        </li>
                    </ul>

                </div>

            </div>
            <div class="col-md-8">

                <div class="panel">
                    <div class="panel-body">
                        <div class="tab-content">

                            <div id="why-use-tracker" class="tab-pane active animated fadeInUp">
                                @include('help.answers.why-use-tracker')
                            </div>

                            <div id="user-access-matrix" class="tab-pane animated fadeInUp">
                                @include('help.answers.access-roles')
                            </div>

                            <div id="answer3" class="tab-pane animated fadeInUp">
                                @include('help.answers.why-use-tracker')
                            </div>

                            <div id="answer4" class="tab-pane animated fadeInUp">
                                @include('help.answers.why-use-tracker')
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
