@extends('application.base-tracker')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Clan Admin
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
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

        <div id="admin-container" class="m-t-lg">

            @if ($requests->count())
                <h4 class="m-t-xl">Approval History <small>Last 3 days</small></h4>
                <hr/>
                <div class="panel panel-filled">
                    <table class="table table-hover basic-datatable">
                        <thead>
                        <tr>
                            <th>Member Name</th>
                            <th>Approver</th>
                            <th class="text-center">Approved at</th>
                            <th class="text-center col-xs-2">Forum Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td><code>{{ $request->member->name }}</code></td>
                                <td>{{ $request->approver->name }}</td>
                                <td class="text-center">{{ $request->approved_at->diffForHumans() }}</td>
                                <td class="text-center">
                                    <a href="{{ doForumFunction([$request->member->clan_id], 'forumProfile') }}"
                                       target="_blank"
                                    >#{{ $request->member->clan_id }}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>
            @endif

            <hr>

            <a href="{{ route('admin.member-request.index') }}" class="btn btn-accent"><i class="fa
            fa-arrow-left"></i> Back to pending</a>
        </div>
    </div>

@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/admin.js?v=1.5') !!}"></script>
@endsection