<td><i class="fa fa-cube text-success"></i>
    {{ $event->user->name }} created platoon
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>