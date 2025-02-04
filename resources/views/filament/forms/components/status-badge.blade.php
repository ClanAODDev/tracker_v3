@php
    $record = $getRecord();

    $status = 'Accepted';
    $color = 'success';
    $icon = 'heroicon-s-check-circle'; // ✅

    if (is_null($record?->approved_at)) {
        $status = 'Awaiting Approval';
        $color = 'warning';
        $icon = 'heroicon-s-clock'; // ⏳
    } elseif (!is_null($record?->approved_at) && is_null($record?->accepted_at)) {
        $status = 'Awaiting Acceptance';
        $color = 'info';
        $icon = 'heroicon-s-information-circle'; // ℹ️
    }
@endphp

<div class="flex items-center space-x-2">
    <x-filament::badge :color="$color" class="text-sm font-semibold px-3 py-1" :icon="$icon" >
        {{ $status }}
    </x-filament::badge>
</div>
