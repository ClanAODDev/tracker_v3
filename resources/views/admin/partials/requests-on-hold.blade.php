@if ($onHold->count())
    <h4 class="m-t-xl">ON HOLD</h4>
    <hr/>
    <div class="panel panel-filled basic-datatable">
        <table class="table">
            <thead>
            <tr>
                <th>Member Name</th>
                <th>Admin</th>
                <th>Division</th>
                <th class="text-center">Hold placed</th>
                <th class="text-center">Reason</th>
                <th class="text-center col-xs-2"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($onHold as $request)
                <tr>
                    <td>
                        <code>{{ $request->member->name }}</code>
                    </td>
                    <td>{{ $request->approver->name }}</td>
                    <td>{{ $request->division->name }}</td>
                    <td class="text-center">
                        {{ $request->hold_placed_at->format('Y-m-d H:i:s') }}
                    </td>
                    <td>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Alias architecto aut corporis delectus
                        earum ex, in ipsam iure, laudantium mollitia natus nobis officiis possimus, ratione recusandae
                        rem sed suscipit tempora.
                    </td>

                    <td>
                        <a class="btn btn-success pull-right"
                           href="{{ route('admin.member-requests.reprocess', $request->id) }}">
                            <small><i class="fa fa-user-plus"></i> APPROVE</small>
                        </a>
                    </td>

                </tr>
            @endforeach
            </tbody>

        </table>
    </div>
@endif