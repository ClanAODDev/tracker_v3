<td>
    <i class="fa fa-comment text-success"></i>
    Note created by {{ $event->user->name }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>
