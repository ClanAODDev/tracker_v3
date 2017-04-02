@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($member->primaryDivision)
                <img src="{{ getDivisionIconPath($member->primaryDivision->abbreviation) }}" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.edit-member-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            {{ $member->position->name  }}
        @endslot
    @endcomponent

    {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member) !!}

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
        <div class="tab-pane active" id="division-info">
            <div class="panel panel-filled">
                @include('member.forms.editProfileForm')
            </div>
        </div>
        <div class="tab-pane fade in" id="handles"></div>
        <div class="tab-pane fade in" id="history"></div>
        <div class="tab-pane fade in" id="user-account"></div>
    </div>

@stop
