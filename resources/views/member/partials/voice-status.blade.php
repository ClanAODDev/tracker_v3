@if($status === 'never_connected')
    <span class="text-muted"><i class="fas fa-users-slash"></i></span>
@endif

@if($status === 'never_configured')
    <span class="text-danger"><i class="fa fa-user-slash"></i></span>
@endif

@if($status === 'connected')
    <span class="text-success"><i class="fa fa-user"></i></span>
@endif

@if($status === 'disconnected')
    <span class="text-danger"><i class="fas fa-sign-out-alt"></i></span>
@endif