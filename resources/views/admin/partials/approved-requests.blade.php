@if ($approved->count())
    <h4 class="m-t-xl">APPROVED REQUESTS</h4>
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
                    <td class="text-center {{ $request->approved_at <= now()->subHour(3) && !$request->processed_at ? 'text-danger' : 'null' }}">
                        {{ $request->approved_at }}
                    </td>

                    @if ($request->processed_at)
                        <td class="text-center"><small>--</small></td>
                        <td class="text-center"><small>--</small></td>
                    @else
                        <td>
                            <a class="btn btn-info btn-block" target="_blank"
                               href="{{ $request->approvePath . $request->name }}">
                                <small><i class="fa fa-user-plus"></i> REPROCESS</small>
                            </a>
                        </td>
                        <td>
                            <form action="{{ route('admin.member-request.requeue', $request) }}"
                                  method="post">
                                {{ csrf_field() }}
                                <button class="btn btn-warning btn-block" type="submit">
                                    <small><i class="fa fa-refresh"></i> REQUEUE</small>
                                </button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>
@endif