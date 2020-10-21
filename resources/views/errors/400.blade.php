@extends('application.base')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                Oops
            @endslot
            @slot('subheading')
                You can't do that
            @endslot
            @slot('icon')
                <i class="pe page-header-icon pe-7s-close-circle"></i>
            @endslot
            @slot('currentPage')
                v3
            @endslot
        @endcomponent

        <div class="panel panel-filled">
            <div class="panel-body">
                Sorry, you're not allowed to do that. If you feel you have received this in error, please contact your division leadership or a clan administrator.
            </div>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>
    </div>

@endsection
