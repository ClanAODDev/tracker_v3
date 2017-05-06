<table class="table table-hover adv-datatable">
    <thead>
    <tr>
        <th>#</th>
        <th>User</th>
        <th>Action</th>
        <th>Subject ID</th>
        <th>Division</th>
        <th>Date</th>
    </tr>
    </thead>

    <tbody>
    @foreach(App\Activity::all() as $event)
        <tr>
            <td>{{ $event->id }}</td>
            <td>{{ $event->user->name }}</td>
            <td>{{ $event->name }}</td>
            <td>{{ $event->subject_id }}</td>
            <td>{{ $event->division->name }}</td>
            <td>{{ $event->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>