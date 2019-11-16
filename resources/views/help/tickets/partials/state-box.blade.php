@if ($ticket->state == 'new')
    <div class="slight text-success">NEW</div>
@elseif ($ticket->state == 'assigned')
    <div class="slight text-warning">IN PROGRESS</div>
@else
    <div class="slight text-default">RESOLVED</div>
@endif
