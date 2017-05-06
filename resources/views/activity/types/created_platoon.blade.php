<i class="fa fa-cube text-success"></i>
{{ $event->user->name }} created platoon {{ $event->subject->name }}
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>