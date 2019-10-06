<div>
    @if ($ticket->state == 'new')
        <small class="label label-success">NEW</small>
    @elseif ($ticket->state == 'assigned')
        <small class="label label-warning">IN PROGRESS</small>
    @else
        <small class="label label-default">RESOLVED</small>
    @endif
</div>