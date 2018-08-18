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
        <p>Approving a request will open a new window, where the member will be processed into AOD. Simultaneously, the request will be approved and the requester will be notified.</p>

        <div id="admin-container" class="m-t-lg">
            <member-requests :requests="{{ $pending }}">
                <i class="fa fa-refresh fa-spin text-info"></i> Loading...
            </member-requests>
        </div>
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/admin.js?v=1.0') !!}"></script>
@stop