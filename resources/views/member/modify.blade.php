@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member ) !!}

    <h2>
        <strong>{!! $member->present()->rankName !!}</strong>
        <small>Edit Profile</small>

        <div class="btn-group btn-group-sm pull-right">
            <a href="{{ action('MemberController@show', $member->clan_id) }}"
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
            <a href="#division-info" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-cog fa-lg"></i><span class="hidden-sm hidden-xs"> Division Info</span>
            </a>
        </li>
        <li>
            <a href="#game-info" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-gamepad fa-lg"></i><span class="hidden-sm hidden-xs"> Sub-games</span>
            </a>
        </li>
        <li>
            <a href="#handles" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-users fa-lg"></i><span class="hidden-sm hidden-xs"> Handles</span>
            </a>
        </li>
        <li class="disabled">
            <a>
                <i class="fa fa-history fa-lg"></i><span class="hidden-sm hidden-xs"> Records</span>
            </a>
        </li>
        <li class="disabled">
            <a>
                <i class="fa fa-key" aria-hidden="true"></i><span class="hidden-sm hidden-xs"> User Account</span>
            </a>
        </li>
    </ul>
    {{-- end profile edit nav --}}

    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade active in" id="division-info">
            <div class="margin-top-20">
                @include('member.forms.editProfileForm')
            </div>
        </div>

    </div>

@stop
