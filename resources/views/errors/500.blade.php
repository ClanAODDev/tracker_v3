@extends('application.base')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                He's dead, Jim!
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
                An application error occurred. Please contact your division leadership or a clan administrator to report the problem.
            </div>
        </div>




        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>
    </div>

@endsection
