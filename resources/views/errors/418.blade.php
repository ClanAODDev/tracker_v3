@extends('application.base-tracker')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                Maintenance
            @endslot
            @slot('subheading')
                Application currently in maintenance mode
            @endslot
            @slot('icon')
                <i class="pe page-header-icon pe-7s-moon"></i>
            @endslot
            @slot('currentPage')
                v3
            @endslot
        @endcomponent

        <div class="panel panel-filled">
            <div class="panel-body">
                The AOD Tracker is currently unavailable due to maintenance or updates being performed. We should be back shortly, though. Hang in there!
            </div>
        </div>
    </div>

@endsection

