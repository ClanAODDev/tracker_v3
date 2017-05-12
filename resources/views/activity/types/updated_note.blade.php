<td>
    <i class="fa fa-comment text-accent"></i>
    {{ $event->user->name }} updated note #{{ $event->subject->id }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>