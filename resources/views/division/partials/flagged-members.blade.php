@if (count($flaggedMembers) > 0)
    <table class="table adv-datatable table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Last Seen
                <small class="text-muted">Days ago</small>
            </th>
            <th class="no-sort"></th>
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
                    @can ('update', $member)
                        <a href="{{ route('member.unflag-inactive', $member->clan_id) . "#flagged" }}"
                           class="btn btn-warning btn-sm">
                            <i class="fa fa-flag"></i>
                            Unflag
                        </a>
                    @endcan
                </td>
                <td>
                    <div class="btn-group-xs">
                        @can ('delete', $member)
                            {!! Form::model($member, ['method' => 'delete', 'route' => ['member.drop-for-inactivity', $member->clan_id]]) !!}
                            <input type="hidden" value="inactivity" name="removal-reason" />
                            <button type="submit" class="btn btn-danger btn-sm remove-member"
                                    data-member-id="{{ $member->clan_id }}">
                                <i class="fa fa-trash text-danger"></i> Remove
                            </button>
                            {!! Form::close() !!}
                        @else
                            <span class="text-muted">No available actions</span>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>There are currently no members flagged for removal.</p>
@endif
