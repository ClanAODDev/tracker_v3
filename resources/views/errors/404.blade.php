@extends('application.base')

@section('content')

    <div class="container-center md animated fadeInDown">
        @component('application.components.view-heading')
            @slot('heading')
                Not Found
            @endslot
            @slot('subheading')
                This is not the page you're looking for
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
               Either something broke, or you have poor typing skills. Either way, the page you tried to reach does not exist. If you feel this is in error, please contact your division leadership or a clan administrator.
            </div>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>
    </div>

@endsection
