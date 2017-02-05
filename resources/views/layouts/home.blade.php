@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <div class="view-header">
                    <div class="pull-right text-right hidden-sm hidden-xs" style="line-height: 14px">
                        <small>AOD Tracker<br>Dashboard<br> <span class="c-white">v3</span>
                        </small>
                    </div>
                    <div class="header-icon">
                        <i class="pe page-header-icon pe-7s-shield"></i>
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs text-uppercase">AOD Tracker</h3>
                        <small>The tracker is a management tool to help leaders organize and maintain the members
                            assigned to their division.
                        </small>
                    </div>
                </div>
                <hr>
            </div>
        </div>


        <div class="row">
            <div class="col-md-8">
                <div class="row my-division">
                    @include('layouts.partials.my-division')
                </div>

                <div class="row divisions">
                    @include('layouts.partials.divisions')
                </div>
            </div>

            <div class="col-md-4">
                @include('layouts.partials.member-census-count')
            </div>
        </div>

    </div>

@stop
