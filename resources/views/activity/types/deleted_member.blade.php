<i class="fa fa-user text-danger"></i>
{{ $event->user->name }} removed {{ $event->subject->name }} from AOD
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>