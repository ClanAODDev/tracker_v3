@extends('application.base-tracker')
@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
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

        <h2>Reprocess Request</h2>
        <p>If you are re-processing a member request that has an approved date that appears red, follow the steps
            below.</p>
        <hr>

        <div class="row">

            <div class="col-md-12">

                <div class="panel panel-filled">
                    <div class="panel-heading c-white"><strong>Step 1:</strong> Add to AOD</div>
                    <div class="panel-body">
                        Re-process the member into AOD manually. This will open the mod-cp in a new window.
                    </div>
                    <div class="panel-footer">
                        <a class="btn btn-info" target="_blank"
                           onclick="enableConfirmation()"
                           href="{{ $request->approvePath . $request->name }}">
                            <i class="fa fa-user-plus"></i> Force Add to AOD
                        </a>
                    </div>
                </div>

            </div>

            <div class="col-md-12">

                <div class="panel panel-filled">
                    <div class="panel-heading c-white"><strong>Step 2:</strong> Confirm added</div>
                    <div class="panel-body">
                        Confirm the member was successfully added. This will update the request with a new approved time
                        and approver, and will take you back to the requests page.
                    </div>
                    <div class="panel-footer">
                        <form action="{{ route('admin.member-requests.reprocess-confirm', $request->id) }}"
                              method="POST">
                            {{ csrf_field() }}
                            <button class="btn btn-default btn-disabled" id="confirmBtn" disabled type="submit">
                                <i class="fas fa-check"></i> Confirm
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
        function enableConfirmation() {
            document.getElementById("confirmBtn").disabled = false;
            document.getElementById('confirmBtn').setAttribute('class', 'btn btn-success');
        }
    </script>

@endsection