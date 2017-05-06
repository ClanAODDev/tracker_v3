<i class="fa fa-cube text-accent"></i>
{{ $event->user->name }} modified platoon {{ $event->subject->name }}
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>