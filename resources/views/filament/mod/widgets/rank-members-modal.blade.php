<style>
    .rmm-count { font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem; }
    .rmm-list { margin: 0 -1.5rem; border-top: 1px solid #e5e7eb; max-height: 28rem; overflow-y: auto; }
    .rmm-row { padding: 0.625rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
    .rmm-name { display: block; font-weight: 500; color: #4f46e5; text-decoration: none; font-size: 0.875rem; }
    .rmm-name:hover { text-decoration: underline; }
    .rmm-unit { font-size: 0.75rem; color: #9ca3af; margin: 0.125rem 0 0; }
    .rmm-empty { display: flex; flex-direction: column; align-items: center; padding: 2.5rem 0; color: #9ca3af; gap: 0.5rem; }

    .dark .rmm-list { border-top-color: #374151; }
    .dark .rmm-row { border-bottom-color: #374151; }
    .dark .rmm-row:hover { background: rgba(255,255,255,0.04); }
    .dark .rmm-name { color: #818cf8; }
    .dark .rmm-unit { color: #6b7280; }
</style>

<p class="rmm-count">{{ $members->count() }} {{ Str::plural('member', $members->count()) }}</p>

@if($members->isEmpty())
    <div class="rmm-empty">
        <x-heroicon-o-user-group style="width: 2.5rem; height: 2.5rem;" />
        <p>No members at this rank.</p>
    </div>
@else
    <div class="rmm-list">
        @foreach($members as $member)
            <div class="rmm-row">
                <a href="{{ route('member', $member->getUrlParams()) }}"
                   target="_blank"
                   class="rmm-name">
                    {{ $member->name }}
                </a>
                <p class="rmm-unit">
                    @if($member->platoon)
                        {{ $member->platoon->name ?? 'Untitled' }}
                        @if($member->squad) &rsaquo; {{ $member->squad->name ?? 'Untitled' }}@endif
                    @else
                        Unassigned
                    @endif
                </p>
            </div>
        @endforeach
    </div>
@endif
