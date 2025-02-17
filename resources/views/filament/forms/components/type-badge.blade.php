@php
    $record = $getRecord();

    $typeColor = $record->rank->isPromotion($record->member->rank)
        ? 'success'
        : 'danger';
    $typeIcon = $record->rank->isPromotion($record->member->rank)
        ? 'heroicon-s-arrow-up'
        : 'heroicon-s-arrow-down';
@endphp

<div class="flex flex-row items-center space-x-4">
    <x-filament::badge :color="$typeColor" class="text-sm font-semibold px-3 py-1" :icon="$typeIcon">
        {{ $record->rank->isPromotion($record->member->rank) ? 'Promo' : 'Demo' }}
    </x-filament::badge>
</div>