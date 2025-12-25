@props([
    'type' => 'info',
    'icon' => null,
    'cta' => null,
    'ctaLabel' => null,
])

@php
    $iconMap = [
        'info' => 'fa-info-circle',
        'warning' => 'fa-exclamation-circle',
        'danger' => 'fa-exclamation-triangle',
        'success' => 'fa-check-circle',
    ];
    $defaultIcon = $iconMap[$type] ?? 'fa-info-circle';
@endphp

<div {{ $attributes->merge(['class' => "notice notice-{$type}"]) }}>
    <i class="fa {{ $icon ?? $defaultIcon }} notice-icon"></i>
    <div class="notice-content">
        {{ $slot }}
    </div>
    @if($cta)
        <a href="{{ $cta }}" class="notice-cta">
            {{ $ctaLabel ?? 'View' }}
            <i class="fa fa-chevron-right"></i>
        </a>
    @endif
</div>
