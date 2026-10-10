@php
    $diagramLevels = $levels->map(fn ($level) => ['label' => $level['label'], 'tier' => $level['tier']])->values()->all();
@endphp
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <p style="font-size: 0.875rem; opacity: 0.7;">Reflects the levels as currently entered, including unsaved changes. The filled box is the leader; the tinted boxes beneath it are the units they cover.</p>
    @foreach ($levels as $index => $level)
        <div style="border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 1rem;">
            <div style="font-weight: 600;">
                {{ $level['leader_title'] ?: 'Leader' }}
                <span style="font-weight: 400; opacity: 0.7;">({{ $level['abbr'] }}) · leads a {{ $level['label'] ?: 'Level ' . $level['depth'] }}</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); gap: 1.5rem; margin-top: 0.75rem; align-items: center;">
                @include('filament.components.unit-hierarchy-diagram', ['levels' => $diagramLevels, 'highlight' => $level['depth']])
                <ul style="margin-left: 1.25rem; list-style: disc; font-size: 0.875rem;">
                    @foreach ($level['powers'] as $power)
                        <li>{{ $power }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
