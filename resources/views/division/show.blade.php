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
                        <h3 class="m-b-xs text-uppercase">{{ $division->name }} Division</h3>
                        <small>
                            {{ $division->description }}
                        </small>
                    </div>
                </div>
                <hr>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('division.partials.leadership')
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                @include('division.partials.platoons')
            </div>
            <div class="col-md-4">

                @include('division.partials.general-sergeants')

                @include('division.partials.staff-sergeants')

            </div>
        </div>
    </div>

    </div>

@stop
