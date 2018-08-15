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
        <div id="admin-container">
            <member-requests :requests="{{ $pending }}">
                <i class="fa fa-refresh fa-spinner text-info"></i> Loading...
            </member-requests>
        </div>

        @if (count($approved))
            <h4 class="m-t-lg">PAST REQUESTS</h4>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Member</th>
                    <th>Division</th>
                    <th>Approved by</th>
                    <th>Date Approved</th>
                </tr>
                </thead>
                @foreach ($approved as $request)
                    <tr>
                        <td><code>AOD_{{ $request->member->name }}</code></td>
                        <td>{{ $request->division->name }}</td>
                        <td>{{ $request->approver->name }}</td>
                        <td>{{ $request->updated_at }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

@stop

@section('footer_scripts')
    <script src="{!! asset('/js/admin.js?v=1.0') !!}"></script>
@stop