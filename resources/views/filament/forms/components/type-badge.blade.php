@php
    $record = $getRecord();

    // Determine if the record is a Promotion or Demotion
    $typeColor = $record->rank->isPromotion($record->member->rank)
        ? 'success' // green for Promotion
        : 'danger'; // red for Demotion

    $typeIcon = $record->rank->isPromotion($record->member->rank)
        ? 'heroicon-s-arrow-up'   // Up arrow for Promotion
        : 'heroicon-s-arrow-down'; // Down arrow for Demotion
@endphp

<div class="flex flex-row items-center space-x-4">
    <x-filament::badge :color="$typeColor" class="text-sm font-semibold px-3 py-1" :icon="$typeIcon">
        {{ $record->rank->isPromotion($record->member->rank) ? 'Promo' : 'Demo' }}
    </x-filament::badge>
</div>
