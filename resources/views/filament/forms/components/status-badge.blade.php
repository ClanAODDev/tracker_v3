@php
    $record = $getRecord();

    $status = 'Approved';
    $color = 'success';
    $icon = 'heroicon-s-check-circle'; // Default: Approved ✅

    if (is_null($record?->approved_at)) {
        $status = 'Awaiting Approval';
        $color = 'warning';
        $icon = 'heroicon-s-clock'; // ⏳ Waiting Icon
    } elseif (!is_null($record?->approved_at) && is_null($record?->accepted_at)) {
        $status = 'Awaiting Acceptance';
        $color = 'info';
        $icon = 'heroicon-s-information-circle'; // ℹ️ Info Icon
    }
@endphp

<div class="flex items-center space-x-2">
    <x-filament::icon
            :name="$icon"
            size="md"
            class="text-{{ $color }}-500 dark:text-{{ $color }}-400"
    />
    <x-filament::badge :color="$color" class="text-sm font-semibold px-3 py-1">
        {{ $status }}
    </x-filament::badge>
</div>
