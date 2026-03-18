@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-4 py-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl font-bold text-sm transition-all duration-300 shadow-sm border border-indigo-100 dark:border-indigo-800'
            : 'flex items-center gap-3 px-4 py-3 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900 hover:text-gray-900 dark:hover:text-white rounded-2xl font-medium text-sm transition-all duration-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($icon))
        <div class="shrink-0 {{ $active ? 'text-indigo-600 dark:text-indigo-400 scale-110' : 'text-gray-400 group-hover:text-gray-600 transition-transform' }}">
            {{ $icon }}
        </div>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
