<table class="table table-hover adv-datatable">
    <thead>
    <tr>
        <th>#</th>
        <th>User</th>
        <th>Action</th>
        <th>Subject ID</th>
        <th>Date</th>
    </tr>
    </thead>

    <tbody>
    @foreach($activityLog as $event)
        <tr>
            <td class="text-muted">{{ $event->id }}</td>
            <td>{{ $event->user->name }}
                <a href="{{ route('member', $event->user->member->clan_id) }}">
                    <i class="fa fa-arrow-circle-right"></i>
                </a>
            </td>
            <td>{{ $event->name }}</td>
            <td>{{ $event->subject_id }}</td>
            <td>{{ $event->created_at->format('d M Y h:i A') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>