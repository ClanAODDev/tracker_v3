<i class="fa fa-gamepad text-danger"></i>
{{ $event->user->name }} deleted the {{ $event->subject->name }} division
<span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>