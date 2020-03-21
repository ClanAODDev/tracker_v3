<td><i class="fa fa-cube text-accent"></i>
    {{ $event->user->name ?? "Somboey" }} updated platoon
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>