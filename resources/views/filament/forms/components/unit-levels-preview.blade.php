<div style="display: flex; flex-direction: column; gap: 0.75rem;">
    @foreach ($levels as $level)
        <div style="margin-left: {{ ($level['depth'] - 1) * 1.5 }}rem; border-left: 3px solid rgb(var(--primary-500)); padding-left: 0.75rem;">
            <div style="font-weight: 600;">
                {{ $level['label'] ?: 'Level ' . $level['depth'] }}
                <span style="font-weight: 400; opacity: 0.7;">· led by a {{ $level['leader_title'] ?: 'leader' }}</span>
            </div>
            @if ($level['covers'])
                <div style="font-size: 0.75rem; opacity: 0.7;">Their powers cover every {{ $level['covers'] }} below them</div>
            @endif
            <ul style="margin: 0.25rem 0 0 1.25rem; list-style: disc; font-size: 0.875rem;">
                @foreach ($level['powers'] as $power)
                    <li>{{ $power }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
