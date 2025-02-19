@php
    $record = $getRecord();
    $targetReached = $record->rank->value === $record->member->rank->value;
@endphp

@if(!$targetReached)
    @php
        $isPromotion = $record->rank->isPromotion($record->member->rank);
        $typeColor   = $isPromotion ? 'success' : 'danger';
        $typeIcon    = $isPromotion ? 'heroicon-s-arrow-up' : 'heroicon-s-arrow-down';
    @endphp
    <x-filament::badge
            :color="$typeColor"
            class="text-sm font-semibold px-3 py-1"
            :icon="$typeIcon" />
@endif
