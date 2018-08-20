@if ($approved->count())
    <h4 class="m-t-xl">APPROVED REQUESTS</h4>
    <p>Approved requests will remain here until the forum member sync occurs. Requests for members with active AOD Member status will automatically be pruned.</p>
    <hr />
    <div class="panel panel-filled">
        <table class="table">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Recruiter</th>
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
                    <td>{{ $request->requester->name }}</td>
                    <td>{{ $request->division->name }}</td>
                    <td class="text-center {{ $request->approved_at <= now()->subHour(3) ? 'text-danger' : 'null' }}">
                        {{ $request->approved_at }}
                    </td>
                    <td>
                        <a href="{{ $request->approvePath . $request->name }}"><i class="fa fa-hammer"></i></a>
                    </td>
                    <td>
                        <form action="{{ route('admin.member-request.requeue', $request) }}"
                              method="post">
                            {{ csrf_field() }}
                            <button class="btn btn-info btn-block" type="submit">
                                <i class="fa fa-refresh"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>

        </table>
    </div>
@endif