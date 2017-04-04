@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member) !!}

    <h2>
        <a href="{{ route('member', $member->clan_id) }}"
           class="btn btn-default" title="Return to member profile">
            <i class="fa fa-angle-left fa-2x"></i>
        </a>
        <strong>{!! $member->present()->rankName !!}</strong>
        <small>Edit Profile</small>
    </h2>
    <hr />

    {{-- Edit profile nav --}}
    <ul class="nav nav-tabs margin-top-20">
        <li class="active">
            <a href="#division-info" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-cog fa-lg"></i><span class="hidden-xs"> Division Info</span>
            </a>
        </li>
        <li>
            <a href="#handles" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-users fa-lg"></i><span class="hidden-xs"> Handles</span>
            </a>
        </li>
        <li>
            <a href="#history" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-history fa-lg"></i><span class="hidden-xs"> Records</span>
            </a>
        </li>
        <li class="disabled pull-right">
            <a href="#user-account" data-toggle="tab" aria-expanded="false">
                <i class="fa fa-key" aria-hidden="true"></i><span class="hidden-xs"> User Account</span>
            </a>
        </li>
    </ul>
    {{-- end profile edit nav --}}


    {{-- division info --}}
    <div id="myTabContent" class="tab-content">
        <div class="tab-pane fade active in" id="division-info">
            <div class="margin-top-20">
                @include('edit-member-form.blade.php')
            </div>
        </div>
        <div class="tab-pane fade in" id="handles"></div>
        <div class="tab-pane fade in" id="history"></div>
        <div class="tab-pane fade in" id="user-account"></div>
    </div>

@stop
