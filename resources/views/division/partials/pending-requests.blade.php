<h4>PENDING <span class="badge">{{ $requests['pending']->count() }}</span></h4>

@if ($requests['pending']->count())
    <div class="panel panel-filled" id="{{ $division->abbreviation }}">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter Name</th>
                    <th>Waiting for</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests['pending'] as $request)
                    <tr>
                        <td>{{ $request->member->name }}</td>
                        <td>{{ $request->requester->name }}</td>
                        <td>{{ $request->created_at ? $request->created_at->diffForHumans(null, true) : 'N/A' }}</td>
                        <td>
                            <form action="{{ route('division.member-requests.cancel', [$division, $request->id]) }}" method="post">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-block btn-danger">Cancel</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <small class="text-muted">Please allow 24/48 hours for requests to be approved.</small>

@else
    <p><span class="text-success">Success!</span> No member requests are currently pending</p>
@endif