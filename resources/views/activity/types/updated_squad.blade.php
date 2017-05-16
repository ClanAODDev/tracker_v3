<td><i class="fa fa-cubes text-accent"></i>
    {{ $event->user->name }} modified {{ $event->subject->name }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>