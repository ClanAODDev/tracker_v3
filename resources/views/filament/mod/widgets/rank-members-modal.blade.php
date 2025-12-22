<div class="space-y-4">
    @if($members->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2">No members with this rank.</p>
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            {{ $members->count() }} {{ Str::plural('member', $members->count()) }}
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
            @foreach($members as $member)
                <div class="flex items-center justify-between py-3 px-2 hover:bg-gray-100/50 dark:hover:bg-white/5 rounded">
                    <div>
                        <a href="{{ route('filament.mod.resources.members.edit', $member) }}"
                           class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                            {{ $member->name }}
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Joined {{ $member->join_date?->diffForHumans() ?? 'Unknown' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
