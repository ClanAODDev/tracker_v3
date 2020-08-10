<h4 class="m-t-lg">PENDING APPROVAL<span class="badge">{{ $requests['pending']->count() }}</span></h4>

@if ($requests['pending']->count())
    <div class="panel panel-filled panel-c-accent" id="{{ $division->abbreviation }}">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter Name</th>
                    <th>Waiting for</th>
                    <th class="text-center">Cancel</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests['pending'] as $request)
                    <tr>
                        <td>{{ $request->name }}</td>
                        <td>{{ $request->requester->name }}</td>
                        <td>{{ ($request->hold_placed_at ? 'ON HOLD - ' . $request->hold_placed_at->format('Y-m-d H:i:s') : $request->created_at) ? $request->created_at->diffForHumans(null, true) : 'N/A' }}</td>
                        <td>
                            <form action="{{ route('division.member-requests.cancel', [$division, $request->id]) }}"
                                  method="post">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-block btn-danger"><i class="fa fa-trash"></i></button>
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