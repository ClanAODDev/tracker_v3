@extends('application.base-tracker')

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
                <p>It looks like something went terribly wrong.</p>

                <p>Report this issue to the clan admins! The error you found is: <code>{{ $exception->getMessage()
                }}</code></p>
            </div>
        </div>


        <div>
            <a href="{{ route('home') }}" class="btn btn-accent">Back to app</a>
        </div>
    </div>

@endsection
