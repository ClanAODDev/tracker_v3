<td>
    <i class="fa fa-cubes text-success"></i>
    {{ $event->user->name }} created platoon {{ $event->subject->name }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>