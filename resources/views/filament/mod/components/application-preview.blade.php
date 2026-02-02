@php
    $fields = collect($getState() ?? []);
@endphp

<div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-6 space-y-5">
    <div class="text-center space-y-1">
        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Application Preview</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">This is how applicants will see the form</p>
    </div>

    <hr class="border-gray-200 dark:border-white/10">

    @forelse ($fields->sortBy('display_order') as $field)
        @if (empty($field['label']))
            @continue
        @endif

        <div class="space-y-1.5">
            <label class="text-sm font-medium text-gray-950 dark:text-white">
                {{ $field['label'] }}
                @if ($field['required'] ?? true)
                    <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </label>

            @if (! empty($field['helper_text']))
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $field['helper_text'] }}</p>
            @endif

            @if (($field['type'] ?? 'text') === 'text')
                <div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-sm text-gray-400 dark:text-gray-500">
                    Short text answer
                </div>

            @elseif ($field['type'] === 'textarea')
                <div class="rounded-lg border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-sm text-gray-400 dark:text-gray-500 min-h-[5rem]">
                    Long text answer
                </div>

            @elseif ($field['type'] === 'radio' && ! empty($field['options']))
                <div class="space-y-1.5 pl-1">
                    @foreach ($field['options'] as $option)
                        @if (! empty($option['label']))
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="inline-flex h-4 w-4 rounded-full border-2 border-gray-300 dark:border-white/20"></span>
                                {{ $option['label'] }}
                            </label>
                        @endif
                    @endforeach
                </div>

            @elseif ($field['type'] === 'checkbox' && ! empty($field['options']))
                <div class="space-y-1.5 pl-1">
                    @foreach ($field['options'] as $option)
                        @if (! empty($option['label']))
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <span class="inline-flex h-4 w-4 rounded border-2 border-gray-300 dark:border-white/20"></span>
                                {{ $option['label'] }}
                            </label>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-6 text-gray-400 dark:text-gray-500">
            <p class="text-sm">No fields defined yet. Add fields above to see a preview.</p>
        </div>
    @endforelse

    @if ($fields->filter(fn ($f) => ! empty($f['label']))->isNotEmpty())
        <div class="pt-2 text-center">
            <span class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white opacity-75 cursor-default">
                Submit Application
            </span>
        </div>
    @endif
</div>
