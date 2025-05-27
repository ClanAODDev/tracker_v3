@php
    $record = $getRecord();

    // Default to "Accepted" state
    $status = 'Accepted';
    $color = 'success';
    $icon = 'heroicon-s-check-circle'; // ✅

    // Check for denial first
    if (!is_null($record?->denied_at)) {
        $status = 'Denied';
        $color = 'danger';
        $icon = 'heroicon-s-x-circle'; // ❌ (or any denial icon)
    } elseif (is_null($record?->approved_at)) {
        // Pending approval
        $status = 'Approval';
        $title = 'Waiting for Approval';
        $color = 'warning';
        $icon = 'heroicon-s-clock'; // ⏳
     } elseif ($record?->rank->isOfficer() && is_null($record?->awarded_at)) {
        // Pending award
        $status = 'Awaiting Award';
        $title = 'Requester action required';
        $color = 'primary';
        $icon = 'heroicon-s-clock'; // ⏳
    } elseif (!is_null($record?->approved_at || $record?->awarded_at) && is_null($record?->accepted_at)) {
        // Pending acceptance
        $status = 'Acceptance';
        $title = 'Waiting for Member Acceptance';
        $color = 'info';
        $icon = 'heroicon-s-information-circle'; // ℹ️
    }

    // Determine if the record is a Promotion or Demotion
    $typeColor = $record->rank->isPromotion($record->member->rank)
        ? 'success' // green for Promotion
        : 'danger'; // red for Demotion

    $typeIcon = $record->rank->isPromotion($record->member->rank)
        ? 'heroicon-s-arrow-up'   // Up arrow for Promotion
        : 'heroicon-s-arrow-down'; // Down arrow for Demotion
@endphp

<x-filament::badge :color="$color" class="text-sm font-semibold px-3 py-1" :icon="$icon" :title="$title ?? null">
    {{ $status }}
</x-filament::badge>
