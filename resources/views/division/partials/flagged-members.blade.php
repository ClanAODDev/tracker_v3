<div class="panel panel-filled collapsed m-t-xl">
    <div class="panel-heading panel-toggle">
        <i class="fa fa-trash text-danger"></i> Manage Flagged
    </div>
    <div class="panel-body">
        <table class="table basic-datatable">
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
            @forelse ($flagged as $member)
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
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash text-danger"></i>
                            </button>
                            {!! Form::close() !!}

                            <a href="#" class="btn btn-green"></a>
                        @else
                            <span class="text-muted">No available actions</span>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">There are currently no members flagged for removal.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>