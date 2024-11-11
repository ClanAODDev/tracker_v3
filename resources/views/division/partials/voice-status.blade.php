@php use App\Enums\DiscordStatus; @endphp

@if($status === DiscordStatus::NEVER_CONNECTED)
    <span class="text-muted">Never connected</span>
@endif

@if($status === \App\Enums\DiscordStatus::NEVER_CONFIGURED)
    <span class="text-warning">Never configured</span>
@endif

@if($status === \App\Enums\DiscordStatus::CONNECTED)
    <span class="text-success">Connected</span>
@endif

@if($status === \App\Enums\DiscordStatus::DISCONNECTED)
    <span class="text-danger">Disconnected</span>
@endif