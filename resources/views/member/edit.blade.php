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
        @include ('application.partials.back-breadcrumb', ['page' => 'profile'])

        <div class="row">
            <div class="col-md-12">
                <div class="panel">

                    <div class="tabs-container">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#member" aria-expanded="true">
                                    <i class="fa fa-user text-info"></i> Information
                                </a>
                            </li>

                            <li>
                                <a data-toggle="tab" href="#user" aria-expanded="false">
                                    <i class="fa fa-lock text-danger"></i> Account
                                </a>
                            </li>

                        </ul>
                        <div class="tab-content" id="profile-container">
                            <div id="member" class="tab-pane active">
                                <div class="panel-body">
                                    <manage-member
                                            :member-id="{{ $member->id }}"
                                            :positions="{{ $positions }}"
                                            :position="{{ $member->position->id }}"></manage-member>
                                </div>
                            </div>

                            <div id="user" class="tab-pane">
                                <div class="panel-body">
                                    @if($member->user)
                                        <manage-user-account
                                                :user-id="{{ $member->user->id }}"
                                                :roles="{{ $roles }}"
                                                :role="{{ $member->user->role->id }}"
                                                username="{{ $member->user->name }}"
                                                e-mail="{{ $member->user->email }}">
                                        </manage-user-account>
                                    @else
                                        <h4><i class="fa fa-times-circle-o text-warning"
                                               aria-hidden="true"></i> No User Account</h4>
                                        <p>There is no user account associated with this profile. This member will have to first register in order to manage these settings.</p>
                                    @endif
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
