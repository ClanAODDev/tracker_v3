@extends('application.base')

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
                        <i class="pe page-header-icon pe-7s-home"></i>
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs text-uppercase">AOD Tracker</h3>
                        <small>Division and member management software</small>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="my-division">
                    @include('home.partials.my-division')
                </div>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-lg-12">
                <h4 class="m-b-xs text-uppercase">Navigate <small>All Divisions</small></h4>
                <hr>
            </div>
        </div>

        <div class="row divisions">
            @include('home.partials.divisions')
        </div>
    </div>
@stop
