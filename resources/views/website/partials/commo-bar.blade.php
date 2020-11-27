<div class="commo-bar">
    <p class="commo-item"><i class="fab fa-teamspeak fa-lg"></i>
        <strong>TEAMSPEAK</strong>
        @if($commo['ts'])
            {{ $commo['ts']->online }} / {{ $commo['ts']->max }}
        @else
            UNKNOWN
        @endif
    </p>
    <p class="commo-item">
        <i class="fab fa-discord fa-lg"></i>
        <strong>DISCORD</strong>
        @if($commo['discord'])
            {{ $commo['discord']->online + $commo['discord']->idle + $commo['discord']->dnd }}
            / {{ $commo['discord']->total }}
        @else
            UNKNOWN
        @endif
    </p>
</div>