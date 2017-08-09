<div class="table-responsive">
    <table class="table table-hover adv-datatable">
        <thead>
        <tr>
            <th>Action</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($activity as $event)
            <tr title="{{ $event->created_at }}">
                <td>
                    {{ $event->user->name or $event->user_id }}
                    {{ $event->name }}
                    <span class="badge">{{ $event->created_at->diffForHumans() }}</span>
                </td>
                <td class="text-right">{{ $event->created_at }}</td>
            </tr>
        @empty
            <tr>
                <td>
                    <i class="fa fa-times-rectangle text-muted"></i>
                    No activity recorded
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>