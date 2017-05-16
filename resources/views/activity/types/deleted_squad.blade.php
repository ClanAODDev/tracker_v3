<td>
    <i class="fa fa-cubes text-danger"></i>
    {{ $event->user->name }} removed platoon {{ $event->subject->name }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>