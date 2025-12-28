@props([
    'type' => 'info',
    'icon' => null,
    'cta' => null,
    'ctaLabel' => null,
    'secondaryCta' => null,
    'secondaryCtaLabel' => null,
    'secondaryCtaClass' => '',
    'secondaryCtaData' => [],
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
    <div class="notice-actions">
        @if($secondaryCta)
            <button
                type="button"
                class="notice-cta notice-cta--secondary {{ $secondaryCtaClass }}"
                data-url="{{ $secondaryCta }}"
                @foreach($secondaryCtaData as $key => $value)
                    data-{{ $key }}="{{ $value }}"
                @endforeach
            >
                {{ $secondaryCtaLabel ?? 'Action' }}
            </button>
        @endif
        @if($cta)
            <a href="{{ $cta }}" class="notice-cta">
                {{ $ctaLabel ?? 'View' }}
                <i class="fa fa-chevron-right"></i>
            </a>
        @endif
    </div>
</div>
