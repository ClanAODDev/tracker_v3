@extends('application.base')
@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="division-header">
                    <div class="header-icon">
                        <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
                    </div>
                    <div class="header-title">
                        <h3 class="m-b-xs text-uppercase">{{ $platoon->name }}</h3>
                        <small>
                            {{ $division->name }} Division
                        </small>
                    </div>
                </div>

                <hr>
            </div>
        </div>

        <div class="row margin-top-20">
            <div class="col-md-8">
                @include('platoon.partials.platoon-members')
            </div>

            <div class="col-md-4">
                {{--@include('platoon.partials.member_stats')--}}
            </div>
        </div>

    </div>
@stop
