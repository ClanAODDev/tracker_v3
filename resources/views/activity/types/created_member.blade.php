<td>
    <i class="fa fa-user text-success"></i>
    {{ $event->user->name or "Somebody" }} recruited {{ $event->subject->name or "Somebody" }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>