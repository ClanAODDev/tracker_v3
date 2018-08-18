
<h4 class="m-t-xl">DENIED <span class="badge">{{ $requests['denied']->count() }}</span></h4>

@if ($requests['denied']->count())
    <div class="panel panel-filled" id="{{ $division->abbreviation }}">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter Name</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($requests['denied'] as $request)
                    <tr>
                        <td>{{ $request->member->name }}</td>
                        <td>{{ $request->requester->name }}</td>
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
@else
    <p><span class="text-success">Success!</span> No member requests are currently pending</p>
@endif