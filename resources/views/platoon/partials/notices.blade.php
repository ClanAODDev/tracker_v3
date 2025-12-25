@cannot('update', $platoon)
    @if (count($platoon->unassigned))
        <x-notice type="warning" icon="fa-users">
            This {{ $division->locality('platoon') }} has <code>{{ count($platoon->unassigned) }}</code> unassigned {{ Str::plural('member', count($platoon->unassigned)) }}.
        </x-notice>
    @endif
@endcannot