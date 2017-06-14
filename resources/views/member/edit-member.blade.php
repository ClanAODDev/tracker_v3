@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($primaryDivision)
                <img src="{{ getDivisionIconPath($primaryDivision->abbreviation) }}" />
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
        @include ('member.partials.notices')

        <div class="row">
            <div class="col-md-12">
                <div class="panel">

                    <div class="tabs-container">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#member" aria-expanded="true">
                                    <i class="fa fa-user"></i> Information
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#handles" aria-expanded="true">
                                    <i class="fa fa-gamepad"></i> Handles
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#part-time" aria-expanded="true">
                                    <i class="fa fa-clock-o"></i> Part-time
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content" id="profile-container">
                            <div id="member" class="tab-pane active">
                                <div class="panel-body">
                                    <manage-member :member-id="{{ $member->id }}"
                                                   :positions="{{ $positions }}"
                                                   :position="{{ $member->position->id }}"
                                    ></manage-member>
                                </div>
                            </div>

                            <div id="handles" class="tab-pane">
                                <div class="panel-body">

                                    <manage-handles :handles="{{ $handles  }}"
                                                    :member-id="{{ $member->id }}"
                                    ></manage-handles>

                                </div>
                            </div>

                            <div id="part-time" class="tab-pane">
                                <div class="panel-body">

                                    @include ('member.partials.edit-part-time')
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
