<div class="table-responsive">
    <table class="table table-hover">
        @forelse ($activity as $event)
            <tr title="{{ $event->created_at }}">
                <td>{{ $event->user->name or $event->user_id }} {{ $event->name }}</td>
                <td class="text-right">{{ $event->created_at->diffForHumans() }}</td>
            </tr>
        @empty
            <tr>
                <td>
                    <i class="fa fa-times-rectangle text-muted"></i>
                    No activity recorded
                </td>
            </tr>
        @endforelse
    </table>
</div>