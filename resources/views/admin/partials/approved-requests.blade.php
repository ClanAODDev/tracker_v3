@if ($approved->count())
    <h4 class="m-t-xl">AWAITING PROCESSING</h4>
    <hr/>
    <div class="panel panel-filled basic-datatable">
        <table class="table">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Approver</th>
                <th>Division</th>
                <th class="text-center">Approved at</th>
                <th class="text-center col-xs-2"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($approved as $request)
                <tr>
                    <td>
                        <code>{{ $request->member->name }}</code>
                    </td>
                    <td>{{ $request->approver->name }}</td>
                    <td>{{ $request->division->name }}</td>
                    <td class="text-center">
                        {{ $request->approved_at->diffForHumans() }}
                        @if($request->approved_at <= now()->subHour(3) && !$request->processed_at)
                            <strong class="text-danger"
                                  title="Member forum status not yet approved."
                            > OVERDUE</strong>
                        @endif
                    </td>

                    @unless($request->processed_at)
                        <td>
                            <a class="btn btn-info btn-block"
                               href="{{ route('admin.member-requests.reprocess', $request->id) }}">
                                <small>REPROCESS <i class="fa fa-arrow-right"></i></small>
                            </a>
                        </td>
                    @endunless
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>
@endif