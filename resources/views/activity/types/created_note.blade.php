<td>
    <i class="fa fa-comment text-success"></i>
    {{ $event->user->name }} added a note to {{ $event->subject->member->name }}'s profile
</td>
<td>
    <span class="pull-right text-muted">{{ $event->created_at->diffForHumans() }}</span>
</td>
