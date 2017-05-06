<i class="fa fa-comment text-accent"></i>
{{ $event->user->name }} updated a note on {{ $event->subject->name }}'s profile
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>