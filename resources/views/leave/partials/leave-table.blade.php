<table class="table table-hover basic-datatable">
    <thead>
    <tr>
        <th class="no-sort"></th>
        <th>Name</th>
        <th>End Date</th>
        <th>Approver</th>
        <th>Reason</th>
        <th class="no-sort">Forum Thread</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($membersWithLeave as $member)
        <tr class="{{ $member->leaveOfAbsence->expired ? 'text-danger' : null }}">
            <td>
                <a href="{{ route('leave.edit', [$member->clan_id, $member->leaveOfAbsence->id]) }}"
                   class="btn btn-default">
                    <i class="fa fa-search"></i>
                </a>
            </td>
            <td>
                {{ $member->name }}
                <span class="text-muted">{{ $member->rank->abbreviation }}</span>
            </td>
            <td title="{{ $member->leaveOfAbsence->end_date->diffForHumans() }}">
                @if($member->leaveOfAbsence->expired)
                    <i class="fa fa-exclamation-triangle text-danger" title="Expired"></i>
                @endif
                {{ $member->leaveOfAbsence->end_date->format('Y-m-d') }}
            </td>
            <td>
                @if ($member->leaveOfAbsence->approver)
                    {{ $member->leaveOfAbsence->approver->name }}
                @else
                    <div class="text-accent">Needs approval</div>
                @endif
            </td>
            <td>
                {{ ucwords($member->leaveOfAbsence->reason) }}
            </td>
            <td>
                <a target="_blank"
                   href="{{ doForumFunction([$member->leaveOfAbsence->note->forum_thread_id], 'showThread') }}">
                    View Thread <i class="fa fa-external-link text-accent"></i>
                </a>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>