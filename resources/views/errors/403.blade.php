@extends('application.base')

@section('content')

    <div class="container-center md animated fadeInDown">
        @component('application.components.view-heading')
            @slot('heading')
                Unauthorized
            @endslot
            @slot('subheading')
                Something went very wrong
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
                Sorry, you're not allowed to do whatever it is that you were trying to do. If you feel you have received this in error, please contact your division leadership or a clan administrator.
            </div>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>
    </div>

@endsection
