@if ($approved->count())
    <h4 class="m-t-xl">APPROVED REQUESTS</h4>
    <hr/>
    <div class="panel panel-filled">
        <table class="table">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Approver</th>
                <th>Division</th>
                <th class="text-center">Approved at</th>
                <th class="text-center col-xs-2">Reprocess</th>
                <th class="text-center col-xs-2">Requeue</th>
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
                    <td class="text-center {{ $request->approved_at <= now()->subHour(3) ? 'text-danger' : 'null' }}">
                        {{ $request->approved_at }}
                    </td>

                    @if ($request->processed_at)
                        <td colspan="2" class="text-center">
                            <small class="text-success ">PROCESSED INTO AOD</small>
                        </td>
                    @else
                        <td>
                            <a class="btn btn-info btn-block" target="_blank"
                               href="{{ $request->approvePath . $request->name }}"><i class="fa fa-user-plus"></i></a>
                        </td>
                        <td>
                            <form action="{{ route('admin.member-request.requeue', $request) }}"
                                  method="post">
                                {{ csrf_field() }}
                                <button class="btn btn-warning btn-block" type="submit">
                                    <i class="fa fa-refresh"></i>
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