<td>
    <i class="fa fa-user text-danger"></i>
    {{ $event->user->name ?? "Somebody" }} removed {{ $event->subject->name }} from AOD
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>