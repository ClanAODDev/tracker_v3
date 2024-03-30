@if($status === 'never_connected')
    <span class="text-muted">Never connected</span>
@endif

@if($status === 'never_configured')
    <span class="text-warning">Never configured</span>
@endif

@if($status === 'connected')
    <span class="text-success">Connected</span>
@endif

@if($status === 'disconnected')
    <span class="text-danger">Disconnected</span>
@endif