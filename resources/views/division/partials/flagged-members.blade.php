@if (count($flaggedMembers) > 0)
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
        @foreach ($flaggedMembers as $member)
            <tr>
                <td>
                    <a href="{{ route('member', $member->clan_id) }}"><i class="fa fa-search"></i></a>
                    {{ $member->name }}
                    <span class="text-muted slight">{{ $member->rank->abbreviation }}</span>
                </td>
                <td><code>{{ $member->last_activity->diffInDays() }}</code></td>
                <td>
                    @can ('delete', $member)
                        {!! Form::model($member, ['method' => 'delete', 'route' => ['member.drop-for-inactivity', $member->clan_id]]) !!}
                        <input type="hidden" value="inactivity" name="removal-reason" />
                        <button type="submit" class="btn btn-danger btn-xs">
                            <i class="fa fa-trash text-danger"></i> Remove
                        </button>
                        {!! Form::close() !!}
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
