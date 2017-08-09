<td>
    <i class="fa fa-cubes text-success"></i>
    {{ $event->user->name }} created squad {{ $event->subject->name or $event->subject->id }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>