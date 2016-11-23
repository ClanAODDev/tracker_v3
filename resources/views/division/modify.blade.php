@extends('layouts.app')
@section('content')

    <h2>
        <strong>{!! $division->name !!}</strong>
        <small>Edit Division</small>

        <div class="btn-group btn-group-sm pull-right">
            <a href="{{ action('DivisionController@show', $division->abbreviation) }}"
               class="btn btn-default"><i class="fa fa-times fa-lg"></i><span class="hidden-sm hidden-xs"> Cancel</span>
            </a>

            <a href="#" class="btn btn-success">
                <i class="fa fa-check fa-lg"></i><span class="hidden-sm hidden-xs"> Save Changes</span>
            </a>
        </div>
    </h2>
    <hr/>

    {{-- Edit profile nav --}}
    <ul class="nav nav-tabs margin-top-20">
        <li class="active">
            <a href="#recruitment-settings" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-user-plus fa-lg"></i><span class="hidden-sm hidden-xs"> Recruiting</span>
            </a>
        </li>
        <li>
            <a href="#locality" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-language fa-lg"></i><span class="hidden-sm hidden-xs"> Locality</span>
            </a>
        </li>
        <li>
            <a href="#slack" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-slack fa-lg"></i><span class="hidden-sm hidden-xs"> Slack Integration</span>
            </a>
        </li>
    </ul>
    {{-- end profile edit nav --}}

    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade active in" id="recruitment-settings">
            <div class="margin-top-20">
                @include('division.forms.recruitmentSettingsForm')
            </div>
        </div>
        <div class="tab-pane fade in" id="locality"></div>
    </div>


@stop


