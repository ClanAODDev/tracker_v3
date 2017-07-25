<div class="panel panel-filled collapsed m-t-xl">
    <div class="panel-heading panel-toggle">
        <i class="fa fa-trash text-danger"></i> Manage Flagged
    </div>
    <div class="panel-body">
        @if (count($flagged) > 0)
            <table class="table basic-datatable table-hover table-condensed">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Last Seen
                        <small class="text-muted">Days ago</small>
                    </th>
                    <th class="no-sort"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($flagged as $member)
                    <tr>
                        <td>
                            {{ $member->name }}
                            <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                        </td>
                        <td><code>{{ $member->last_activity->diffInDays() }}</code></td>
                        <td>
                            @can ('delete', App\Member::class)
                                {{-- TODO - add delete form to inactive members --}}
                                {!! Form::model($member, ['method' => 'delete', 'route' => ['deleteMember', $member->clan_id]]) !!}
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                                {!! Form::close() !!}

                                <a href="#" class="btn btn-green"></a>
                            @else
                                <span class="text-muted">No available actions</span>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>There are currently no members flagged for removal.</p>
        @endif
    </div>
</div>