<td>
    <i class="fa fa-gamepad text-success"></i>
    {{ $event->user->name }} created the <span class="c-white">{{ $event->subject->name }}</span> division
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>