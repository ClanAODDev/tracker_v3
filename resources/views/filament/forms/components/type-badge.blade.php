@php
    $record = $getRecord();
    $previousRank = $record->member->rank ?? null;

    if (!$previousRank) {
        $typeColor = 'primary';
        $typeIcon = 'heroicon-s-plus-circle';
        $badgeText = 'Recruitment';
    } else {
        $isPromotion = $record->rank->isPromotion($previousRank);
        $typeColor = $isPromotion ? 'success' : 'danger';
        $typeIcon = $isPromotion ? 'heroicon-s-arrow-up' : 'heroicon-s-arrow-down';
        $badgeText = $isPromotion ? 'Promo' : 'Demo';
    }
@endphp

<div class="flex flex-row items-center space-x-4">
    <x-filament::badge :color="$typeColor" class="text-sm font-semibold px-3 py-1" :icon="$typeIcon">
        {{ $badgeText }}
    </x-filament::badge>
</div>