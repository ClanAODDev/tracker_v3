<td>
    <i class="fa fa-comment text-accent"></i>
    {{ $event->user->name or "Somebody" }} updated note
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>