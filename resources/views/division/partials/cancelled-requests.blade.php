@if ($requests['cancelled']->count())
    <h4 class="m-t-xl">CANCELLED <span class="badge">{{ $requests['cancelled']->count() }}</span></h4>
    <div class="panel panel-filled panel-c-danger" id="{{ $division->abbreviation }}">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter Name</th>
                    <th>Cancelled By</th>
                    <th>When</th>
                    <th class="text-center">Review</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests['cancelled'] as $request)
                    <tr>
                        <td>{{ $request->name }}</td>
                        <td>{{ $request->requester->name }}</td>
                        <td>{{ $request->canceller->name }}</td>
                        <td>{{ $request->cancelled_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('division.member-requests.edit', [$division, $request]) }}"
                               class="btn btn-info btn-block"><i class="fa fa-search"></i></a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif