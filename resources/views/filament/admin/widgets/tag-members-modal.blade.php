<div class="space-y-4">
    @if($members->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-heroicon-o-user-group class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2">No members have this tag.</p>
        </div>
    @else
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            {{ $members->count() }} {{ Str::plural('member', $members->count()) }} with this tag
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
            @foreach($members as $member)
                <div class="flex items-center justify-between py-3 px-2 hover:bg-gray-100/50 dark:hover:bg-white/5 rounded">
                    <div class="flex items-center gap-3">
                        <div>
                            <a href="{{ route('filament.admin.resources.members.edit', $member) }}"
                               class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                {{ $member->name }}
                            </a>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $member->rank->getLabel() }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($member->division)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $member->division->name }}
                            </span>
                        @else
                            <span class="text-sm text-gray-400">No division</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
