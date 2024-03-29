@extends('application.base-tracker')

@section('content')

    <div class="container-center md">
        @component('application.components.view-heading')
            @slot('heading')
                He's dead, Jim!
            @endslot
            @slot('subheading')
                No Primary Division
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
                <p>You are no longer associated with a primary division, and cannot access the tracker. Please speak
                    with your intended division leadership to resolve this issue.</p>

                @if(session('impersonating'))
                    <p class="text-muted">You appear to be impersonating. Try refreshing the page.</p>
                @endif
            </div>

        </div>
    </div>

@endsection
