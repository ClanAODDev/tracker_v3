@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="division-header">
                    <div class="header-icon">
                        <img src="{{ getDivisionIconPath($member->primaryDivision->abbreviation) }}"/>
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs text-uppercase">{!! $member->present()->rankName !!}</h3>
                        @if ($member->position)
                            <small>
                                {{ $member->position->name }}
                            </small>
                        @endif
                    </div>
                </div>
                <hr>
            </div>
        </div>

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
                                    <a href="#" class="btn btn-default btn-sm"><i class="fa fa-envelope"></i>
                                        Contact</a>
                                    <a href="#" class="btn btn-default btn-sm"><i class="fa fa-check"></i> Check
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
