<td>
    <i class="fa fa-cubes text-success"></i>
    {{ $event->user->name ?? "Somebody" }} created squad {{ $event->subject->name ?? $event->subject->id }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>