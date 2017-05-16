<td>
    <i class="fa fa-user text-success"></i>
    {{ $event->user->name }} recruited {{ $event->subject->name }}
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>