<td>
    <i class="fa fa-lock text-accent"></i>
    {{ $event->user->name or "Sombody" }} updated <span class="c-white">{{ $event->subject->name }}</span>'s role
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>