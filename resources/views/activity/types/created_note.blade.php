<td>
    <i class="fa fa-comment text-success"></i>
    {{ $event->user->name ?? "Somebody" }} created note
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>
