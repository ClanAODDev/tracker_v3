{{ $event->user->name }} modified {{ $event->subject->name }}
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>