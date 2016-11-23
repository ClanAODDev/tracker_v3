@extends('layouts.app')
@section('content')

    <h2>
        <a href="{{ action('DivisionController@show', $division->abbreviation) }}"
           class="btn btn-default" title="Return to division page">
            <i class="fa fa-angle-left fa-2x"></i>
        </a>
        <strong>{!! $division->name !!}</strong>
        <small>Edit Division</small>
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
    <div class="row margin-top-20">
        <form id="division-settings" method="post"
              action="{{ action('DivisionController@update', $division->abbreviation) }}">
            {{ method_field('PATCH') }}
            <div class="col-md-8">
                <div id="recruitmentForm" class="tab-content">
                    <div class="well">

                        <div class="tab-pane fade active in" id="recruitment-settings">
                            @include('division.forms.recruitmentSettingsForm')
                        </div>

                        <div class="tab-pane fade in" id="locality">

                        </div>

                        <div class="tab-pane fade in" id="slack">

                        </div>

                        <button type="submit" method="post" class="btn btn-success btn-lg text-right">Save Changes</button>

                    </div>

                </div>
            </div>


            {{ csrf_field() }}
        </form>
    </div>



@stop


