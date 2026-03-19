@props([
    'title' => '',
    'subtitle' => '',
])

<div class="space-y-6">
    {{-- Header & Filters --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div>
            @if($title)
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="text-[10px] text-gray-500 uppercase font-black tracking-widest mt-1">{{ $subtitle }}</p>
            @endif
            
            {{-- Extra filter slot (e.g. Loket Tabs) --}}
            @if(isset($extraFilters))
                <div class="mt-4">
                    {{ $extraFilters }}
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row items-stretch gap-3 w-full lg:w-auto">
            {{-- Date Filter --}}
            <div class="relative min-w-[160px]">
                <input 
                    type="date" 
                    wire:model.live="filter_date"
                    class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-2xl text-xs font-bold focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm text-gray-700 dark:text-gray-300"
                >
                <div class="absolute left-3.5 top-3.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-72">
                <input 
                    type="text" 
                    wire:model.live="search" 
                    placeholder="Cari..." 
                    class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 rounded-2xl text-xs font-bold focus:ring-emerald-500 focus:border-emerald-500 transition-all shadow-sm text-gray-700 dark:text-gray-300"
                >
                <div class="absolute left-3.5 top-3.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white dark:bg-gray-800/50 backdrop-blur-md rounded-[2rem] shadow-2xl border border-gray-100 dark:border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto overflow-y-visible">
            <table class="w-full text-left border-separate border-spacing-y-2">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50">
                    <tr>
                        {{ $thead }}
                    </tr>
                </thead>
                <tbody class="divide-y-0 relative">
                    {{ $tbody }}
                </tbody>
            </table>
        </div>
        
        @if(isset($pagination))
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                {{ $pagination }}
            </div>
        @endif
    </div>

    {{-- Mobile View --}}
    <div class="md:hidden space-y-4">
        {{ $mobile }}
        
        @if(isset($pagination))
            <div class="pt-2">
                {{ $pagination }}
            </div>
        @endif
    </div>
</div>
