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
            <td>{{ $event->user->name }}</td>
            <td>{{ $event->name }}</td>
            <td>{{ $event->subject->name or "ID #" . $event->subject_id }}</td>
            <td>{{ $event->created_at->format('Y-m-d H:i A') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>