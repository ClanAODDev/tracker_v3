@extends('layouts.app')
@section('content')

    <div class="row">
        <div class="col-xs-1 text-center">
            <a href="{{ action('DivisionController@show', $division->abbreviation) }}"
               class="btn btn-default btn-lg" title="Return to division page">
                <i class="fa fa-angle-left fa-2x"></i>
            </a>
        </div>
        <div class="col-xs-11">

            <h2>
                @include('division.partials.icon')
                <strong>{!! $division->name !!}</strong>
                <small>Edit Division</small>
            </h2>
            <hr/>

            {{-- Edit profile nav --}}
            <ul class="nav nav-tabs margin-top-20">
                <li class="active">
                    <a href="#recruitment-settings" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-sliders fa-lg"></i><span class="hidden-xs"> General</span>
                    </a>
                </li>
                <li>
                    <a href="#locality" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-language fa-lg"></i><span class="hidden-xs"> Locality</span>
                    </a>
                </li>
                <li>
                    <a href="#recruitment-settings" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-user-plus fa-lg"></i><span class="hidden-xs"> Recruiting</span>
                    </a>
                </li>
                <li>
                    <a href="#slack" data-toggle="tab" aria-expanded="false">
                        <i class="fa fa-slack fa-lg"></i><span class="hidden-xs"> Slack</span>
                    </a>
                </li>
            </ul>
            {{-- end profile edit nav --}}
            <div class="margin-top-20">

                <div id="settings-form" class="tab-content">

                    <div class="tab-pane fade active in" id="recruitment-settings">
                        @include('division.forms.generalSettingsForm')
                    </div>

                    <div class="tab-pane fade in" id="recruiting-settings">
                        @include('division.forms.recruitingSettingsForm')
                    </div>

                    <div class="tab-pane fade in" id="locality">
                        @include('division.forms.localityForm')
                    </div>

                    <div class="tab-pane fade in" id="slack">
                        @include('division.forms.slackForm')
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop