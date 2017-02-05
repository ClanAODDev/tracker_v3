@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="view-header">
                    <div class="pull-right text-right hidden-sm hidden-xs" style="line-height: 14px">
                        <small>AOD Division Tracker<br>Dashboard<br> <span class="c-white">v3</span></small>
                    </div>
                    <div class="header-icon">
                        <i class="pe page-header-icon pe-7s-shield"></i>
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs">AOD Division Tracker</h3>
                        <small>The tracker is a management tool to help leaders organize and maintain the members
                            assigned to their division.
                        </small>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <div class="row my-division">
            @include('layouts.partials.my-division')
        </div>

        <div class="row divisions">
            @include('layouts.partials.divisions')
        </div>

    </div>

@stop
