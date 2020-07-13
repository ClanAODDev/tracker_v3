@extends('application.base')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Member Status Requests
        @endslot
    @endcomponent

    <div class="container-fluid">

        <h2>Member Requests</h2>
        <p>Approving a request will open a new window to complete in-processing on the Clan AOD forums. Simultaneously, the request will be approved and the requester will be notified.
            Denied requests will be sent back to the requester for modification.</p>

        <p class="text-accent">Ensure popups are not blocked for this domain. An additional screen will appear during approval that takes you to Clan AOD forum MODCP.</p>
        <div id="admin-container" class="m-t-lg">
            <member-requests :pending="{{ $pending }}">
                <i class="fas fa-redo-alt"></i> Loading...
            </member-requests>

            @include('admin.partials.approved-requests')
            @include('admin.partials.requests-on-hold')

        </div>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/admin.js?v=1.4') !!}"></script>
@stop