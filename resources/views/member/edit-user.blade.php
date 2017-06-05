@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position->name  }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('application.partials.back-breadcrumb', ['page' => 'profile', 'link' => route('member', $member->clan_id)])

        <div class="row">
            <div class="col-md-12">
                <div class="panel">

                    <div class="tabs-container">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#user" aria-expanded="false">
                                    <i class="fa fa-lock text-danger"></i> Account
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content" id="profile-container">
                            <div id="user" class="tab-pane active">
                                <div class="panel-body">

                                    <manage-user-account
                                            :user-id="{{ $member->user->id }}"
                                            :roles="{{ $roles }}"
                                            :role="{{ $member->user->role->id }}"
                                            username="{{ $member->user->name }}"
                                            e-mail="{{ $member->user->email }}">
                                    </manage-user-account>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        @can('delete', $member)
            {!! Form::model($member, ['method' => 'delete', 'route' => ['deleteMember', $member->clan_id]]) !!}
            @include('member.forms.remove-member-form')
            {!! Form::close() !!}
        @endcan
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/manage-member.js?v=4.4') !!}"></script>
@stop
