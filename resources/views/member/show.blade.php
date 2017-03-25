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

        {!! Breadcrumbs::render('member', $member->primaryDivision, $member->platoon, $member) !!}

        <div class="row m-t-sm">
            <div class="col-md-12">
                <div class="panel panel-filled">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="media">
                                    <i class="pe pe-7s-user c-accent fa-3x"></i>
                                    <h2 class="m-t-xs m-b-none">
                                        Luna user
                                    </h2>
                                    <small>
                                        There are many variations of passages of Lorem Ipsum available, but the majority
                                        have suffered alteration in some form Ipsum available.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <table class="table small m-t-sm">
                                    <tbody>
                                    <tr>
                                        <td>
                                            <strong class="c-white">122</strong> Projects
                                        </td>
                                        <td>
                                            <strong class="c-white">42</strong> Active project
                                        </td>

                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="c-white">61</strong> Comments
                                        </td>
                                        <td>
                                            <strong class="c-white">84</strong> Articles
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong class="c-white">244</strong> Tags
                                        </td>
                                        <td>
                                            <strong class="c-white">42</strong> Friends
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-3 m-t-sm">
                                <span class="c-white">
                                    Contact with user
                                </span>
                                <br>
                                <small>
                                    Lorem Ipsum is simply dummy text of the printing and typesetting industry
                                </small>
                                <div class="btn-group m-t-sm">
                                    <a href="#" class="btn btn-default btn-sm"><i
                                                class="fa fa-envelope"></i>
                                        Contact</a>
                                    <a href="#" class="btn btn-default btn-sm"><i
                                                class="fa fa-check"></i> Check
                                        availability</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
