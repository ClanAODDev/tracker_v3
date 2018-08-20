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
            <span class="text-accent">Denied requests</span> will be sent back to the requester for modification.</p>
        <div id="admin-container" class="m-t-lg">
            <member-requests :pending="{{ $pending }}">
                <i class="fa fa-refresh fa-spin text-info"></i> Loading...
            </member-requests>

            @include('admin.partials.approved-requests')
        </div>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/admin.js?v=1.0') !!}"></script>
@stop